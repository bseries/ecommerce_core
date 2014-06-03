<?php
/**
 * Boutique Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace ecommerce_core\models;

use cms_billing\extensions\finance\Price;
use cms_core\extensions\cms\Settings;
use ecommerce_core\models\CartPositions;
use cms_core\models\Users;
use cms_core\models\VirtualUsers;
use DateTime;
use lithium\analysis\Logger;

class Carts extends \cms_core\models\Base {

	public static $enum = [
		'status' => [
			'open',
			'closed',
			'expired'
		]
	];

	protected $_meta = [
		'source' => 'ecommerce_carts'
	];

	protected static $_actsAs = [
		'cms_core\extensions\data\behavior\Timestamp'
	];

	public $hasMany = [
		'CartPositions' => [
			'class' => 'ecommerce_core\models\CartPositions',
			'key' => 'ecommerce_cart_id'
		]
	];

	public function user($entity) {
		if ($entity->user_id) {
			return Users::findById($entity->user_id);
		}
		return VirtualUsers::findById($entity->virtual_user_id);
	}

	public function order($entity, array $query = []) {
		return Orders::find('first', [
			'conditions' => [
				'ecommerce_cart_id' => $entity->id
			]
		] + $query);
	}

	public function positions($entity) {
		return CartPositions::find('all', [
			'conditions' => [
				'ecommerce_cart_id' => $entity->id
			]
		]);
	}

	public function totalQuantity($entity) {
		return array_reduce($this->positions($entity)->data(), function($carry, $item) {
			return $carry += $item['quantity'];
		}, 0);
	}

	public function totalAmount($entity, $user, $taxZone) {
		$sum = null;

		foreach ($entity->positions() as $position) {
			$result = $position->totalAmount($user, $taxZone);

			if ($sum) {
				$sum = $sum->add($result);
			} else {
				$sum = $result;
			}
		}
		return $sum ?: new Price(0, $user->billing_currency, 'net', $taxZone);
	}

	public function totalTax($entity, $user, $taxZone) {
		return $entity->totalAmount($user, $taxZone)->getTax();
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
}

?>