<?php
/**
 * Bureau eCommerce
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace cms_ecommerce\models;

use cms_core\extensions\cms\Settings;
use cms_ecommerce\models\CartPositions;
use SebastianBergmann\Money\Money;
use SebastianBergmann\Money\Currency;
use cms_core\models\Users;
use cms_core\models\VirtualUsers;
use DateTime;

class Carts extends \cms_core\models\Base {

	public static $enum = [
		'status' => [
			'open',
			'closed'
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
			'class' => 'cms_ecommerce\models\CartPositions',
			'key' => 'ecommerce_cart_id'
		]
	];

	public function user($entity) {
		if ($entity->user_id) {
			return Users::findById($entity->user_id);
		}
		return VirtualUsers::findById($entity->virtual_user_id);
	}

	public function order($entity) {
		return Orders::findByEcommerceCartId($entity->id);
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

	public function totalAmount($entity, $user, $type, $taxZone, $currency) {
		// @todo check if input is net or gross and adjust if needed.
		$result = new Money(0, new Currency($currency));

		foreach ($this->positions($entity) as $position) {
			$result = $result->add($position->totalAmount($user, $type, $taxZone, $currency));
		}
		return $result;
	}

	public function isExpired($entity) {
		$date = DateTime::createFromFormat('Y-m-d H:i:s', $entity->modified);
		return strtotime(Settings::read('checkout.expire'), $date->getTimestamp()) < time();
	}
}

?>