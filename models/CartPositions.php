<?php
/**
 * Bureau eCommerce
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace ecommerce_core\models;

use ecommerce_core\models\Products;

class CartPositions extends \cms_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_cart_positions'
	];

	protected static $_actsAs = [
		'cms_core\extensions\data\behavior\Timestamp'
	];

	public $belongsTo = [
		'Cart' => [
			'class' => 'ecommerce_core\models\Cart',
			'key' => 'ecommerce_cart_id'
		]
	];

	public function product($entity) {
		return Products::findById($entity->ecommerce_product_id);
	}

	public function totalAmount($entity, $user, $type, $taxZone, $currency) {
		$product = $this->product($entity);
		$result = $product->price($user, $type, $taxZone, $currency)->multiply($entity->quantity);

		return $result;
	}
}

?>