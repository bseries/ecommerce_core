<?php
/**
 * Magasin Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace ecommerce_core\models;

use cms_core\extensions\cms\Settings;
use cms_core\extensions\cms\Features;
use cms_core\models\Addresses;
use ecommerce_core\models\ShippingMethods;
use lithium\analysis\Logger;
use li3_mailer\action\Mailer;
use lithium\g11n\Message;

class Shipments extends \cms_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_shipments'
	];

	protected static $_actsAs = [
		'cms_core\extensions\data\behavior\Timestamp',
		'cms_core\extensions\data\behavior\ReferenceNumber',
		'cms_core\extensions\data\behavior\StatusChange'
	];

	public static $enum = [
		'status' => [
			'created',
			'shipping-scheduled',
			'shipping-error',
			'shipping',
			'shipped',
			'delivered'
		]
	];

	public static function init() {
		$model = static::_object();

		static::behavior('cms_core\extensions\data\behavior\ReferenceNumber')->config(
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

	public function order($entity) {
		return Orders::find('first', [
			'conditions' => [
				'ecommerce_shipment_id' => $entity->id
			]
		]);
	}

	public function address($entity) {
		$data = $entity->data();
		$data += $entity->order()->data(); // Add user fields.

		return Addresses::createFromPrefixed('address_', $data);
	}

	public function statusChange($entity, $from, $to) {
		extract(Message::aliases());

		switch ($to) {
			case 'shipped':
				$order = $entity->order();
				$positions = $order->cart()->positions();

				foreach ($positions as $position) {
					$product = $position->product();
					$product->decrement('stock', $position->quantity);

					if (!$product->save()) {
						return false;
					}
					$message  = "Shipment status changed to `shipped`, decremented stock ";
					$message .= "for product {$product->id} by {$position->quantity}. ";
					$message .= "Stock is now `{$product->stock}`.";
					Logger::write('debug', $message);
				}
				if (!Features::enabled('shipment.sendShippedMail')) {
					return true;
				}
				$user = $order->user();

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
}

Shipments::init();

?>