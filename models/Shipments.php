<?php
/**
 * eCommerce Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * Licensed under the AD General Software License v1.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *
 * You should have received a copy of the AD General Software
 * License. If not, see https://atelierdisko.de/licenses.
 */

namespace ecommerce_core\models;

use AD\Finance\Price;
use AD\Finance\Price\Prices;
use DateTime;
use Exception;
use base_address\models\Addresses;
use base_address\models\Contacts;
use base_core\extensions\cms\Settings;
use ecommerce_core\ecommerce\shipping\Methods as ShippingMethods;
use lithium\analysis\Logger;
use lithium\aop\Filters;
use lithium\core\Libraries;
use lithium\g11n\Message;
use lithium\util\Text;

// Shipments are very similar to invoices in that
// they also have positions. In general shipments
// only track amount/values to allow calculating
// best shipment method if required.
//
// Position price can be thought of the "value".
//
// When new positions are added to a newly created
// shipment, the stock for the products (if available)
// must be reserved manually. After shipment creation
// that process is automated.
//
// @see billing_invoice\models\Invoices
class Shipments extends \base_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_shipments'
	];

	public $belongsTo = [
		'User' => [
			'to' => 'base_core\models\Users',
			'key' => 'user_id'
		]
	];

	public $hasOne = [
		'Order' => [
			'to' => 'ecommerce_core\models\Orders',
			'key' => 'ecommerce_shipment_id'
		]
	];

	public $hasMany = [
		'Positions' => [
			'to' => 'ecommerce_core\models\ShipmentPositions',
			'key' => 'ecommerce_shipment_id'
		],
	];

	protected $_actsAs = [
		'base_core\extensions\data\behavior\RelationsPlus',
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\ReferenceNumber',
		'base_core\extensions\data\behavior\StatusChange',
		'base_core\extensions\data\behavior\Searchable' => [
			'fields' => [
				'User.name',
				'User.number',
				'number',
				'tracking',
				'method',
				'status',
				'address_recipient',
				'address_organization',
				'modified',
				'site'
			]
		]
	];

	public static $enum = [
		'status' => [
			'created',
			'cancelled',

			// Shipping is used *only* when there is
			// remote shipping handling. User schedules shipping,
			// it is picked up than switches to "shipping".
			// Once we receive the remote confirmation we
			// switch to "shipped".
			'shipping-scheduled',
			'shipping',

			// Used diretly when manually shipping. Stock
			// reservation transfer happens here. Also
			// notifications are sent here.
			'shipped',

			'shipping-error'
		]
	];

	public static function init() {
		extract(Message::aliases());

		$model = static::object();

		static::behavior('base_core\extensions\data\behavior\ReferenceNumber')->config(
			Settings::read('shipment.number')
		);

		$model->validates['user_id'] = [
			'notEmpty' => [
				'notEmpty',
				'on' => ['create'],
				'message' => $t('This field cannot be empty.', ['scope' => 'ecommerce_core'])
			]
		];

		if (!static::behavior('ReferenceNumber')->config('generate')) {
			$model->validates['number'] = [
				'notEmpty' => [
					'notEmpty',
					'on' => ['create', 'update'],
					'last' => true,
					'message' => $t('This field cannot be empty.', ['scope' => 'ecommerce_core'])
				],
				'isUnique' => [
					'isUniqueReferenceNumber',
					'on' => ['create', 'update'],
					'message' => $t('This number is already in use.', ['scope' => 'ecommerce_core'])
				]
			];
		}
	}

	public function title($entity) {
		return $entity->number;
	}

	public function date($entity) {
		return DateTime::createFromFormat('Y-m-d', $entity->date);
	}

	public function method($entity) {
		return ShippingMethods::registry($entity->method);
	}

	public function address($entity) {
		return Addresses::createFromPrefixed('address_', $entity->data());
	}

	public function statusChange($entity, $from, $to) {
		Logger::write('debug', "Changing shipment status `{$from}`->`{$to}`.");

		switch ($to) {
			case 'cancelled':
				foreach ($entity->positions() as $position) {
					if (!$product = $position->product()) {
						$message  = "Failed to get product for shipment ({$entity->id}) position with `{$position->description}`. ";
						$message .= "Cannot return stock automatically.";
						Logger::write('debug', $message);
						continue;
					}
					if ($from === 'shipped') {
						if (!$product->putStock((integer) $position->quantity)) {
							return false;
						}
					} else {
						if (!$product->unreserveStock((integer) $position->quantity)) {
							return false;
						}
					}
				}
				return true;
			case 'shipped':
				$user = $entity->user();
				$order = $entity->order();

				foreach ($entity->positions() as $position) {
					if (!$product = $position->product()) {
						$message  = "Failed to get product for shipment ({$entity->id}) position with `{$position->description}`. ";
						$message .= "Cannot return stock automatically.";
						Logger::write('debug', $message);
						continue;
					}

					// Transfer stock into taken state. Unreserve must come first
					// as stock checks will otherwise prevent us taking stock.
					if (!$product->unreserveStock($position->quantity)) {
						return false;
					}
					if (!$product->takeStock($position->quantity)) {
						return false;
					}
				}
				return true;
			default:
				break;
		}
		return true;
	}

	public function isCancelable($entity) {
		return in_array($entity->status, [
			'created',
			'cancelled',
			'shipping-scheduled',
			'shipping-error'
		]);
	}

	public function unprefixTrackingCode($entity){
		return static::_trackingParts($entity->tracking)['trackingCode'];
	}

	public function prefixTrackingLink($entity){
		$links = [
			'dhl' => 'https://www.dhl.de/de/privatkunden/pakete-empfangen/verfolgen.html?
					piececode={:trackingCode}',
			'post' => 'https://www.deutschepost.de/sendung/simpleQueryResult.html?
					form.einlieferungsdatum_tag={:day}&
					form.einlieferungsdatum_monat={:month}&form.einlieferungsdatum_jahr={:year}&
					form.sendungsnummer={:trackingCode}',
			'dpd' => 'https://tracking.dpd.de/parcelstatus?query={:trackingCode}'
		];
		$code = Shipments::_trackingParts($entity->tracking);
		if (!isset($code['service'])) {
			return '';
		}
		return Text::insert($links[$code['service']], $code);
	}

	protected static function _trackingParts($code) {
		$reg = "#^(?'service'\w+)\://(?'trackingCode'\w+)(?:\:(?'year'\d{4})(?'month'\d{2})(?'day'\d{2}))?$#";
		if (!preg_match($reg, $code, $matches)){
			return null;
		}
		foreach ($matches as $key => $value) {
			if (is_numeric($key)) {
				unset($matches[$key]);
			}
		}
		return $matches + [
			'service' => null,
			'trackingCode' => null,
			'year' => null,
			'month' => null,
			'day' => null,
			'year' => null
		];
	}

	// This is the total value of the shipment. Used i.e. for
	// calculating the inssurrance value needed.
	public function totals($entity) {
		$result = new Prices();

		foreach ($entity->positions() as $position) {
			$result = $result->add($position->total());
		}
		return $result;
	}

	public function exportAsPdf($entity) {
		extract(Message::aliases());

		$stream = fopen('php://temp', 'w+');

		$user = $entity->user();

		$document = Libraries::locate('document', 'Shipment');
		$document = new $document();

		$sender = Contacts::create(Settings::read('contact.shipping'));

		$subjectAndTitle = $t('Shipment #{:number}', [
			'number' => $entity->number,
			'locale' => $user->locale,
			'scope' => 'ecommerce_core'
		]);

		$document
			->type($t('Shipment', [
				'scope' => 'ecommerce_core',
				'locale' => $user->locale
			]))
			->entity($entity)
			->recipient($user)
			->sender($sender)
			->subject($subjectAndTitle);

		$document->compile();

		$document
			->metaAuthor($sender->name)
			->metaTitle($subjectAndTitle);

		$document->render($stream);

		rewind($stream);
		return $stream;
	}

	/* Deprecated */

	public function totalAmount($entity) {
		throw new Exception('Replaced by totals().');
	}
}

Filters::apply(Shipments::class, 'save', function($params, $next) {
	$entity = $params['entity'];
	$data =& $params['data'];

	if (!$entity->exists()) {
		$entity->user_id = $entity->user_id ?: $data['user_id'];
		if (!$user = $entity->user()) {
			return false;
		}
		$data = $user->address('shipping')->copy($data, 'address_');

		if (empty($data['terms'])) {
			$terms = Settings::read('ecommerce.shipmentTerms');
			$data['terms'] = !is_bool($terms) ? (is_callable($terms) ? $terms($user) : $terms) : null;
		}
	}

	if (!$result = $next($params)) {
		return false;
	}

	// Save nested positions.
	$new = isset($data['positions']) ? $data['positions'] : [];
	foreach ($new as $key => $value) {
		if ($key === 'new') {
			continue;
		}
		// On nested forms id is always present, but on create empty.
		if (!empty($value['id'])) {
			$item = ShipmentPositions::find('first', [
				'conditions' => ['id' => $value['id']]
			]);

			if (!$item) {
				$message  = "Got update request for shipment position {$value['id']}, ";
				$message .= "but position is not in database anymore; skipping.";
				Logger::write('notice', $message);
				return true;
			}

			if ($value['_delete']) {
				if (!$item->delete()) {
					return false;
				}
				if (!$product = $item->product()) {
					$message  = "Failed to get product for shipment ({$entity->id}) ";
					$message  = "position with `{$item->description}`. ";
					$message .= "Cannot unreserve stock automatically.";
					Logger::write('debug', $message);
					continue;
				}
				if (!$product->unreserveStock((integer) $item->quantity)) {
					return false;
				}
				continue;
			}
			if (!$item->save($value)) {
				return false;
			}
		} else {
			$item = ShipmentPositions::create([
				'ecommerce_shipment_id' => $entity->id
			]);
			if (!$item->save($value)) {
				return false;
			}

			if (!$product = $item->product()) {
				$message  = "Failed to get product for shipment ({$entity->id}) ";
				$message  = "position with `{$item->description}`. ";
				$message .= "Cannot reserve stock automatically.";
				Logger::write('debug', $message);
				continue;
			}
			// If adding a new position/product and setting status
			// to shipped at the same time, the product will be
			// reserved here than - as the behavior comes after
			// this filter - tranferred to taken.
			if (!$product->reserveStock((integer) $item->quantity)) {
				return false;
			}
		}
	}
	return true;
});

Filters::apply(Shipments::class, 'delete', function($params, $next) {
	$entity = $params['entity'];
	$result = $next($params);

	if ($result) {
		$positions = ShipmentPositions::find('all', [
			'conditions' => ['ecommerce_shipment_id' => $entity->id]
		]);
		foreach ($positions as $position) {
			$position->delete();
		}
	}
	return $result;
});

Shipments::init();


?>
