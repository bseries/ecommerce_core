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

use lithium\g11n\Message;
use ecommerce_core\models\Products;

class ProductAttributes extends \base_core\models\Base {

	public static $enum = [
		'key' => [
			'size',
			'color',
			'format'
		]
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

	public function title($entity) {
		extract(Message::aliases());

		$map = [
			'size' => $t('size', ['scope' => 'ecommerce_core']),
			'color' => $t('color', ['scope' => 'ecommerce_core']),
			'format' => $t('format', ['scope' => 'ecommerce_core'])
		];
		if (!isset($map[$entity->key])) {
			return $entity->key;
		}
		return $map[$entity->key];
	}
}

?>