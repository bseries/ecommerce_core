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

use ecommerce_core\models\Carts;
use ecommerce_core\models\Products;
use lithium\aop\Filters;

class CartPositions extends \base_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_cart_positions'
	];

	protected $_actsAs = [
		'base_core\extensions\data\behavior\RelationsPlus',
		'base_core\extensions\data\behavior\Timestamp'
	];

	public $belongsTo = [
		'Cart' => [
			'to' => 'ecommerce_core\models\Carts',
			'key' => 'ecommerce_cart_id'
		],
		'Product' => [
			'to' => 'ecommerce_core\models\Products',
			'key' => 'ecommerce_product_id'
		]
	];

	public function amount($entity, $user) {
		return $entity->product()->price($user, $entity->method)->amount();
	}

	public function total($entity, $user) {
		return $entity->product()
			->price($user, $entity->method)
			->amount()
			->multiply($entity->quantity);
	}

	// Checks if the positions quantity is available in stock.
	public function checkStock($entity) {
		return $entity->product()->stock() < (integer) $entity->quantity;
	}

	/* Deprecated */

	public function totalAmount($entity, $user) {
		trigger_error('CartPositions::totalAmount has been deprecated in favor of total().', E_USER_DEPRECATED);
		return $entity->total($user);
	}
}

// Whenever we update a position update the parent's modified
// field, too. This allows us to generate cache keys on the parents
// last modified record.
Filters::apply(CartPositions::class, 'save', function($params, $next) {
	if (!$result = $next($params)) {
		return false;
	}
	return Carts::touchTimestamp($params['entity']->ecommerce_cart_id, 'modified');
});

Filters::apply(CartPositions::class, 'delete', function($params, $next) {
	if (!$result = $next($params)) {
		return false;
	}
	return Carts::touchTimestamp($params['entity']->ecommerce_cart_id, 'modified');
});

// When a position is added, updated or deleted, sync stock on related product.
// We assume that the product for a position is never updated.
Filters::apply(CartPositions::class, 'save', function($params, $next) {
	$data =& $params['data'];
	$entity =& $params['entity'];

	if ($entity->exists()) {
		// We are updating an existing quanity, and will need to
		// reserve/unreserve the difference of old and new.

		if (isset($data['quantity'])) {
			$old = $self::find('first', [
				'conditions' => ['id' => $entity->id]
			]);
			if (!$old->product()->unreserveStock($old->quantity)) {
				return false;
			}
		}
	}
	// If the entity exists we can blindly reserve the whole stock.

	if (!$entity->product()->reserveStock($entity->quantity)) {
		return false;
	}
	if (!$result = $next($params)) {
		return false;
	}
	return Carts::touchTimestamp($entity->ecommerce_cart_id, 'modified');
});

Filters::apply(CartPositions::class, 'delete', function($params, $next) {
	$entity =& $params['entity'];
	$cart = $entity->ecommerce_cart_id;

	if (!$entity->product()->unreserveStock($entity->quantity)) {
		return false;
	}
	if (!$result = $next($params)) {
		return false;
	}
	return Carts::touchTimestamp($cart, 'modified');
});



?>
