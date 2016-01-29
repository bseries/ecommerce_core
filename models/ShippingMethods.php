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
 * License. If not, see http://atelierdisko.de/licenses.
 */

namespace ecommerce_core\models;

use lithium\core\Environment;
use lithium\util\Collection;
use billing_core\extensions\financial\Price;

class ShippingMethods extends \base_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	protected $_actsAs = [
		'base_core\extensions\data\behavior\Access'
	];

	protected static $_data = [];

	public static function register($name, array $data) {
		trigger_error('ShippingMethods is deprecated in favor of ecommerce\ShippingMethods.', E_USER_DEPRECATED);

		$data += [
			'id' => $name,
			'name' => $name,
			'title' => function($locale) {
				return null;
			},
			'access' => ['user.role:admin'],
			'delegate' => false,
			'price' => function($user, $cart) {
				return new Price(0, 'EUR', 'net');
			}
		];
		$data['access'] = (array) $data['access'];
		static::$_data[$name] = static::create($data);
	}

	public static function find($type, array $options = []) {
		trigger_error('ShippingMethods is deprecated in favor of ecommerce\ShippingMethods.', E_USER_DEPRECATED);

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

	public function title($entity) {
		trigger_error('ShippingMethods is deprecated in favor of ecommerce\ShippingMethods.', E_USER_DEPRECATED);

		$value = $entity->data('title');

		if (is_string($value)) {
			return $value;
		}
		return $value(Environment::get('locale'));
	}

	public function price($entity, $user, $cart) {
		trigger_error('ShippingMethods is deprecated in favor of ecommerce\ShippingMethods.', E_USER_DEPRECATED);

		$value = $entity->data('price');
		return $value($user, $cart);
	}
}

?>