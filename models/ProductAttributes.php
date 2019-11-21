<?php
/**
 * eCommerce Core
 *
 * Copyright (c) 2014 David Persson - All rights reserved.
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
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