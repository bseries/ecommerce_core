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

use Exception;
use AD\Finance\Price\Prices;
use AD\Finance\Money\Monies;
use base_core\extensions\cms\Settings;
use ecommerce_core\models\CartPositions;
use DateTime;
use lithium\analysis\Logger;

class Carts extends \base_core\models\Base {

	public static $enum = [
		'status' => [
			'open',
			'closed',
			'expired',
			'cancelled'
		]
	];

	protected $_meta = [
		'source' => 'ecommerce_carts'
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
			'key' => 'ecommerce_cart_id'
		]
	];

	public $hasMany = [
		'Positions' => [
			'to' => 'ecommerce_core\models\CartPositions',
			'key' => 'ecommerce_cart_id'
		]
	];

	protected $_actsAs = [
		'base_core\extensions\data\behavior\RelationsPlus',
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\StatusChange',
		'base_core\extensions\data\behavior\Searchable' => [
			'fields' => [
				'User.name',
				'User.number',
				'status',
				'modified',
				'site',
				'Order.number'
			]
		]
	];

	// We need the user to determine if she has access to the price.
	public function totals($entity, $user) {
		$result = new Prices();

		foreach ($entity->positions() as $position) {
			$result = $result->add($position->total($user));
		}
		return $result;
	}

	public function totalQuantity($entity) {
		return array_reduce($this->positions($entity)->data(), function($carry, $item) {
			return $carry += $item['quantity'];
		}, 0);
	}

	// Returns the totals by currency only, adding differing tax rated prices together,
	// but keeping differing currency distinct. Returns these as a Monies object.
	public function totalValues($entity, $type, $user) {
		$compare = new Monies();
		$byMethod = 'get' . ucfirst($type);

		foreach ($entity->totals($user)->sum() as $rate => $currencies) {
			foreach ($currencies as $currency => $price) {
				$compare = $compare->add($price->{$byMethod}());
			}
		}
		return $compare;
	}

	// Not all carts must have an associated order, i.e.
	// when checkout never begun.
	public static function expire() {
		$data = static::find('all', [
			'conditions' => [
				'status' => 'open'
			]
		]);
		foreach ($data as $item) {
			if ($item->isExpired()) {
				if (!$item->save(['status' => 'expired'])) {
					return false;
				}
				// Stock for reserved products is automatically unreserved
				// in the Carts::statusChange() method.
				Logger::write('debug', "Cart `{$item->id}` expired.");
			}
		}
	}

	public function isExpired($entity) {
		if ($entity->status !== 'open') {
			return false;
		}
		$date = DateTime::createFromFormat('Y-m-d H:i:s', $entity->modified);
		return strtotime(Settings::read('checkout.expire'), $date->getTimestamp()) < time();
	}

	public function statusChange($entity, $from, $to) {
		switch ($to) {
			case 'cancelled':
				// Carts can only be cancelled if there isn't already an order for it.
				if ($entity->order()) {
					return false;
				}
				// Fall through.
			case 'expired':
				// Once cart has shipment that is used for reservations.
				// Instead of checking the cart's status will query of a shipment.
				if (!$entity->order() || !$entity->order()->shipment()) {
					foreach ($entity->positions() as $position) {
						if (!$position->product()->unreserveStock($position->quantity)) {
							return false;
						}
					}
				}
				return true;
			default:
				break;
		}
		return true;
	}

	// Adds given quantity to an already existing position, or creates one with given
	// quantity. Does not save position.
	public function preparePosition($entity, $productId, $quantity, $method) {
		$data = [
			'ecommerce_product_id' => $productId,
			'ecommerce_cart_id' => $entity->id,
			'method' => $method
		];
		if (!$position = CartPositions::find('first', ['conditions' => $data])) {
			$position = CartPositions::create($data);
		}
		$position->quantity += $quantity;
		return $position;
	}

	/* Deprecated */

	public function totalAmount($entity, $user) {
		throw new Exception("Carts::totalAmount() has been deprecated in favor of totals().");
	}

	public function totalTax($entity, $user) {
		throw new Exception("Carts::totalTax has been deprecated.");
	}
}

?>
