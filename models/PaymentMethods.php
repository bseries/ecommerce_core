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

use lithium\core\Environment;
use lithium\util\Collection;
use li3_access\security\Access;
use AD\Finance\Price\NullPrice;

class PaymentMethods extends \base_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	protected static $_data = [];

	public static function register($name, array $data) {
		$data += [
			'id' => $name,
			'name' => $name,
			'title' => function($locale) {
				return null;
			},
			'access' => ['user.role:admin'],
			'price' => function($user, $cart) {
				return new NullPrice();
			},
			'info' => function($context, $format, $renderer, $order) {
				// Dependent on $format return either HTML or plaintext.
			}
		];
		$data['access'] = (array) $data['access'];
		static::$_data[$name] = static::create($data);
	}

	public static function find($type, array $options = []) {
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

	public function title($entity, $locale = null) {
		$locale = $locale ?: Environment::get('locale');
		$value = $entity->data('title');

		if (is_string($value)) {
			return $value;
		}
		return $value($locale);
	}

	public function hasAccess($entity, $user) {
		return Access::check('entity', $user, ['request' => $entity], [
			'rules' => $entity->data('access')
		]) === [];
	}

	public function price($entity, $user, $cart) {
		$value = $entity->data('price');
		return $value($user, $cart);
	}

	public function info($entity, $context, $format, $renderer, $order) {
		$value = $entity->data('info');
		return $value($context, $format, $renderer, $order);
	}
}

?>