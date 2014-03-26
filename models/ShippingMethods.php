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

use cms_billing\extensions\financial\Price;
use lithium\util\Collection;

class ShippingMethods extends \cms_core\models\Base {

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
			'legible' => function($user) {
				return false;
			},
			'delegate' => false,
			'price' => function($user, $cart, $taxZone) {
				return new Price(0, 'EUR', 'net', $taxZone);
			}
		];
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

	public function isLegibleFor($entity, $user) {
		$method = $entity->data('legible');
		return $method($user);
	}

	public function price($entity, $user, $cart, $taxZone) {
		$value = $entity->data('price');
		return $value($user, $cart, $taxZone);
	}
}

?>