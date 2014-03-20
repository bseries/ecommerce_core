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

use SebastianBergmann\Money\Money;
use SebastianBergmann\Money\Currency;
use lithium\util\Collection;

class ShippingMethods extends \cms_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	protected static $_data = [];

	public static function register($name, array $data) {
		$data += [
			'id' => $name,
			'title' => null,
			'legible' => function($user) {
				return false;
			},
			'delegate' => false,
			'price' => function($user, $cart, $type, $taxZone, $currency) {
				return new Money(0, new Currency($currecny));
			}
		];
		static::$_data[$name] = static::create($data);
	}

	public static function find($type, array $options = array()) {
		if ($type == 'all') {
			return new Collection(['data' => static::$_data]);
		} elseif ($type == 'first') {
			return static::$_data[$options['conditions']['id']];
		}
	}

	public function isLegibleFor($entity, $user) {
		$method = $entity->data('legible');
		return $method($user);
	}

	public function price($entity, $user, $cart, $type, $taxZone, $currency) {
		$value = $entity->data('price');
		return $value($user, $cart, $type, $taxZone, $currency);
	}
}

?>