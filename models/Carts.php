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

use AD\Finance\Price;
use base_core\extensions\cms\Settings;
use ecommerce_core\models\CartPositions;
use DateTime;
use lithium\analysis\Logger;

class Carts extends \base_core\models\Base {

	use \base_core\models\UserTrait;

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

	protected static $_actsAs = [
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\StatusChange'
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

	public function order($entity, array $query = []) {
		if ($query) {
			trigger_error('Carts::order with query has been deprecated.', E_USER_DEPRECATED);
		}
		if ($entity->order && !$query) {
			return $entity->order;
		}
		return Orders::find('first', [
			'conditions' => [
				'ecommerce_cart_id' => $entity->id
			]
		] + $query);
	}

	public function positions($entity) {
		return $entity->positions ?: CartPositions::find('all', [
			'conditions' => [
				'ecommerce_cart_id' => $entity->id
			]
		]);
	}

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
				$item->save(['status' => 'expired']);
				Logger::write('debug', "Cart `{$item->id}` expired.");
			}
		}
	}

	public function isExpired($entity) {
		$date = DateTime::createFromFormat('Y-m-d H:i:s', $entity->modified);
		return strtotime(Settings::read('checkout.expire'), $date->getTimestamp()) < time();
	}

	public function statusChange($entity, $from, $to) {
		switch ($to) {
			case 'cancelled':
				return !$entity->order();
			default:
				break;
		}
		return true;
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