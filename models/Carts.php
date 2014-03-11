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

use cms_ecommerce\models\CartPositions;
use SebastianBergmann\Money\Money;
use SebastianBergmann\Money\Currency;

class Carts extends \cms_core\models\Base {

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

	public function totalAmount($entity) {
		$result = new Money(0, new Currency('EUR'));

		foreach ($this->positions($entity) as $position) {
			$result = $result->add($position->totalAmount());
		}
		return $result;
	}
}

?>