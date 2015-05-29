<?php
/**
 * eCommerce Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace ecommerce_core\models;

use Exception;
use lithium\core\Libraries;
use lithium\g11n\Message;
use lithium\analysis\Logger;
use li3_mailer\action\Mailer;

use base_core\extensions\cms\Settings;
use base_address\models\Contacts;
use base_address\models\Addresses;
use ecommerce_core\models\ShippingMethods;

// Shipments are very similar to invoices in that
// they also have positions. In general shipments
// only track amount/values to allow calculating
// best shipment method if required.
//
// Position price can be thought of the "value".
//
// @see billing_core\models\Invoices
class Shipments extends \base_core\models\Base {

	use \base_core\models\UserTrait;

	protected $_meta = [
		'source' => 'ecommerce_shipments'
	];

	public $belongsTo = [
		'User' => [
			'to' => 'base_core\models\Users',
			'key' => 'user_id'
		],
		'VirtualUser' => [
			'to' => 'base_core\models\VirtualUsers',
			'key' => 'virtual_user_id'
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

	protected static $_actsAs = [
		'base_core\extensions\data\behavior\RelationsPlus',
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\ReferenceNumber',
		'base_core\extensions\data\behavior\StatusChange',
		'base_core\extensions\data\behavior\Searchable' => [
			'fields' => [
				'number',
				'tracking',
				'method',
				'status',
				'address_recipient',
				'address_organization',
				'modified',
				'User.number',
				'VirtualUser.number'
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
		$model = static::_object();

		static::behavior('base_core\extensions\data\behavior\ReferenceNumber')->config(
			Settings::read('shipment.number')
		);
	}

	public function method($entity) {
		return ShippingMethods::find('first', [
			'conditions' => [
				'id' => $entity->method
			]
		]);
	}

	public function address($entity) {
		return Addresses::createFromPrefixed('address_', $entity->data());
	}

	public function statusChange($entity, $from, $to) {
		extract(Message::aliases());
		Logger::write('debug', "Changing shipment status `{$from}`->`{$to}`.");

		switch ($to) {
			case 'cancelled':
				foreach ($entity->positions() as $position) {
					$product = $position->product();

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
				$order = $entity->order();
				$user = $order->user();
				$positions = $order->cart()->positions();

				foreach ($positions as $position) {
					$product = $position->product();

					// Transfer stock into taken state.
					if (!$product->takeStock($position->quantity)) {
						return false;
					}
					if (!$product->unreserveStock($position->quantity)) {
						return false;
					}
				}

				if (!Settings::read('shipment.sendShippedMail')) {
					return true;
				}
				if (!$user->is_notified) {
					return true;
				}
				return Mailer::deliver('shipment_shipped', [
					'library' => 'ecommerce_core',
					'to' => $user->email,
					'subject' => $t('Order #{:number} shipped.', [
						'locale' => $user->locale,
						'scope' => 'ecommerce_core',
						'number' => $order->number
					]),
					'data' => [
						'user' => $user,
						'order' => $order,
						'shipment' => $entity
					]
				]);
				break;
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

	public function exportAsPdf($entity) {
		extract(Message::aliases());

		$stream = fopen('php://temp', 'w+');

		$user = $entity->user();

		$document = Libraries::locate('document', 'Shipment');
		$document = new $document();

		$sender = Contacts::create(Settings::read('contact.shipping'));

		$document
			->type($t('Shipment', [
				'scope' => 'ecommerce_core',
				'locale' => $user->locale
			]))
			->invoice($entity)
			->recipient($user)
			->sender($sender)
			->subject($t('Shipment #{:number}', [
				'number' => $entity->number,
				'locale' => $user->locale,
				'scope' => 'ecommerce_core'
			]));

		$document->compile();
		$document->render($stream);

		rewind($stream);
		return $stream;
	}

	/* Deprecated */

	public function totalAmount($entity) {
		throw new Exception('Replaced by totals().');
	}
}

Shipments::applyFilter('save', function($self, $params, $chain) {
	if (!$result = $chain->next($self, $params, $chain)) {
		return false;
	}
	$entity = $params['entity'];
	$data = $params['data'];
	$user = $entity->user();

	// Save nested positions.
	$new = isset($data['positions']) ? $data['positions'] : [];
	foreach ($new as $key => $data) {
		if ($key === 'new') {
			continue;
		}
		if (isset($data['id'])) {
			$item = ShipmentPositions::find('first', ['conditions' => ['id' => $data['id']]]);

			if ($data['_delete']) {
				if (!$item->delete()) {
					return false;
				}
				continue;
			}
		} else {
			$item = ShipmentPositions::create($data + [
				'ecommerce_shipment_id' => $entity->id,
				$user->isVirtual() ? 'virtual_user_id' : 'user_id' => $user->id
			]);
		}
		if (!$item->save($data)) {
			return false;
		}
	}
	return true;
});

Shipments::applyFilter('delete', function($self, $params, $chain) {
	$entity = $params['entity'];
	$result = $chain->next($self, $params, $chain);

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