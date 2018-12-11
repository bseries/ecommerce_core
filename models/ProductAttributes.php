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
 * License. If not, see https://atelierdisko.de/licenses.
 */

namespace ecommerce_core\models;

use ecommerce_core\ecommerce\product\Attributes;

class ProductAttributes extends \base_core\models\Base {

	public static $enum = [
		'key' => [/* Populated from Attributes registry. */]
	];

	protected $_meta = [
		'source' => 'ecommerce_product_attributes'
	];

	protected $_actsAs = [
		'base_core\extensions\data\behavior\RelationsPlus'
	];

	public $belongsTo = [
		'Products' => [
			'to' => 'ecommerce_core\models\Products',
			'key' => 'ecomerce_product_id'
		]
	];

	public static function init() {
		static::$enum['key'] = Attributes::registry(true)->map(function($v) {
			return $v->name();
		});
	}

	public function title($entity) {
		return Attributes::registry($entity->key)->title();
	}
}

ProductAttributes::init();

?>