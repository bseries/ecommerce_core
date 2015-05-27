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

use li3_access\security\Access;
use AD\Finance\Price\NullPrice;
use lithium\util\Collection;

class PaymentMethods {

	protected static $_data = [];

	public static function findForUser($user) {

	}

	public static function register($name, $lazy) {
		static::$_data[$name] = $lazy;
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

	protected static function _lazy($name, $user, $context, $format) {


		$data += [
			'id' => $name,
			'name' => $name,
			'title' => null,
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

	public function title($entity) {
		return $entity->title;
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