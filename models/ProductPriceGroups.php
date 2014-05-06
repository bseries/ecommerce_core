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

use SebastianBergmann\Money\Money;
use SebastianBergmann\Money\Currency;

class ProductPriceGroups extends \cms_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	protected static $_data = [];

	public static function register($name, array $data) {
		$data += [
			'id' => $name,
			'title' => null,
			'access' => ['user.role:admin']
		];
		$data['access'] = (array) $data['access'];
		static::$_data[$name] = static::create($data);
	}

	public static function find($type, array $options = array()) {
		if ($type == 'all') {
			return static::$_data;
		} elseif ($type == 'first') {
			return static::$_data[$options['conditions']['id']];
		}
	}
}

?>