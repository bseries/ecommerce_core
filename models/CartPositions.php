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

use ecommerce_core\models\Products;
use ecommerce_core\models\Carts;

class CartPositions extends \base_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_cart_positions'
	];

	protected static $_actsAs = [
		'base_core\extensions\data\behavior\Timestamp'
	];

	public $belongsTo = [
		'Cart' => [
			'class' => 'ecommerce_core\models\Cart',
			'key' => 'ecommerce_cart_id'
		]
	];

	public function product($entity) {
		return Products::find('first', [
			'conditions' => [
				'id' => $entity->ecommerce_product_id
			]
		]);
	}

	public function amount($entity, $user) {
		return $entity->product()->price($user)->amount();
	}

	public function totalAmount($entity, $user) {
		return $entity->amount($user)->multiply($entity->quantity);
	}
}

//
// Whenever we update a position update the parent's modified
// field, too. This allows us to generate cache keys on the parents
// last modified record.
//
CartPositions::applyFilter('save', function($self, $params, $chain) {
	if (!$result = $chain->next($self, $params, $chain)) {
		return false;
	}
	return Carts::touchTimestamp($params['entity']->ecommerce_cart_id, 'modified');
});
CartPositions::applyFilter('delete', function($self, $params, $chain) {
	if (!$result = $chain->next($self, $params, $chain)) {
		return false;
	}
	return Carts::touchTimestamp($params['entity']->ecommerce_cart_id, 'modified');
});


?>