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

use lithium\g11n\Message;
use ecommerce_core\models\Products;

class ProductStocks extends \base_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_product_stocks'
	];

	public $belongsTo = [
		'Products' => [
			'to' => 'ecommerce_core\models\Products',
			'key' => 'ecomerce_product_id'
		]
	];
}

?>