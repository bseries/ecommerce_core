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

use billing_core\models\TaxTypes;

class ProductPriceGroups extends \base_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	protected static $_data = [];

	public static function register($name, array $data) {
		$data += [
			'id' => $name,
			'title' => null,
			'amountCurrency' => 'EUR',
			'amountType' => 'gross',
			'taxType' => null,
			'access' => ['user.role:admin']
		];
		$data['access'] = (array) $data['access'];
		static::$_data[$name] = static::create($data);
	}

	public static function find($type, array $options = []) {
		if ($type == 'all') {
			return static::$_data;
		} elseif ($type == 'first') {
			return static::$_data[$options['conditions']['id']];
		}
	}

	public function taxType($entity) {
		return TaxTypes::find('first', ['conditions' => ['id' => $entity->taxType]]);
	}
}

?>