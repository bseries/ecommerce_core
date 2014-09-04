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

use billing_core\extensions\financial\Price;
use lithium\util\Collection;
use li3_access\security\Access;

class ShippingMethods extends \base_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	protected static $_data = [];

	public function title($entity) {
		return $entity->title;
	}

	public static function register($name, array $data) {
		$data += [
			'id' => $name,
			'name' => $name,
			'title' => null,
			'access' => ['user.role:admin'],
			'delegate' => false,
			'price' => function($user, $cart, $taxZone) {
				return new Price(0, 'EUR', 'net', $taxZone);
			}
		];
		$data['access'] = (array) $data['access'];
		static::$_data[$name] = static::create($data);
	}

	public static function find($type, array $options = array()) {
		if ($type == 'all') {
			return new Collection(['data' => static::$_data]);
		} elseif ($type == 'first') {
			return static::$_data[$options['conditions']['id']];
		} elseif ($type == 'list') {
			$results = [];

			foreach (static::$_data as $item) {
				$results[$item->id] = $item->title();
			}
			return $results;
		}
	}

	public function hasAccess($entity, $user) {
		return Access::check('entity', $user, ['request' => $entity], [
			'rules' => $entity->data('access')
		]) === [];
	}

	public function price($entity, $user, $cart, $taxZone) {
		$value = $entity->data('price');
		return $value($user, $cart, $taxZone);
	}
}

?>