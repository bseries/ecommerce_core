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

use base_core\extensions\cms\Settings;
use base_core\extensions\cms\Features;
use base_core\models\Addresses;
use ecommerce_core\models\ShippingMethods;
use lithium\analysis\Logger;
use li3_mailer\action\Mailer;
use lithium\g11n\Message;
use billing_core\models\TaxZones;
use Finance\Price;

// Shipments are very similar to invoices in that
// they also have positions. In general shipments
// only track amount/values to allow calculating
// best shipment method if required.
//
// Position price can be thought of the "value".
//
// @see billing_core\models\Invoices
class Shipments extends \base_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_shipments'
	];

	protected static $_actsAs = [
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\ReferenceNumber',
		'base_core\extensions\data\behavior\StatusChange'
	];

	public static $enum = [
		'status' => [
			'created',
			'cancelled',
			'shipping', // When entering state will decrement stock.
			'shipping-scheduled',
			'shipping-error',
			'shipped',
			'delivered'
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

	public function user($entity) {
		return $entity->order()->user();
	}

	public function order($entity, array $query = []) {
		return Orders::find('first', [
			'conditions' => [
				'ecommerce_shipment_id' => $entity->id
			]
		] + $query);
	}

	public function address($entity) {
		return Addresses::createFromPrefixed('address_', $entity->data());
	}

	public function statusChange($entity, $from, $to) {
		extract(Message::aliases());

		switch ($to) {
			case 'cancelled':
				// If shipment went through `shipping` status
				// we must increment stock back again.
				if (strpos($from, 'ship') === 0) {
					$order = $entity->order();
					$positions = $order->cart()->positions();

					foreach ($positions as $position) {
						$product = $position->product();
						$product->increment('stock', $position->quantity);

						if (!$product->save()) {
							return false;
						}
						$message  = "Shipment status changed to `cancelled`, incrementing stock ";
						$message .= "for product {$product->id} by {$position->quantity}. ";
						$message .= "Stock is now `{$product->stock}`.";
						Logger::write('debug', $message);
					}
				}
				return true;
			case 'shipping':
				$order = $entity->order();
				$positions = $order->cart()->positions();

				foreach ($positions as $position) {
					$product = $position->product();
					$product->decrement('stock', $position->quantity);

					if (!$product->save()) {
						return false;
					}
					$message  = "Shipment status changed to `shipping`, decremented stock ";
					$message .= "for product {$product->id} by {$position->quantity}. ";
					$message .= "Stock is now `{$product->stock}`.";
					Logger::write('debug', $message);
				}
				return true;
			case 'shipped':
				if (!Features::enabled('shipment.sendShippedMail')) {
					return true;
				}
				$order = $entity->order();
				$user = $order->user();

				if (!$user->is_notified) {
					return true;
				}
				return Mailer::deliver('shipment_shipped', [
					'to' => $user->email,
					'subject' => $t('Order #{:number} shipped.', [
						'number' => $order->number
					]),
					'data' => [
						'user' => $user,
						'order' => $order,
						'item' => $entity
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

	public function positions($entity) {
		return !$entity->id ? [] : ShipmentPositions::find('all', [
			'conditions' => [
				'ecommerce_shipment_id' => $entity->id
			]
		]);
	}

	public function taxZone($entity) {
		return TaxZones::create([
			'rate' => $entity->tax_rate,
			'note' => $entity->tax_note
		]);
	}

	// This is the total value of the shipment. Used i.e. for
	// calculating the inssurrance value needed.
	public function totalAmount($entity) {
		$result = new Price(0, 'EUR', 'net', $entity->taxZone());

		$positions = $this->positions($entity);

		foreach ($positions as $position) {
			$result = $result->add($position->totalAmount($entity->taxZone()));
		}
		return $result;
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