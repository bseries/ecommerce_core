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

use lithium\g11n\Message;
use base_core\extensions\cms\Widgets;
use ecommerce_core\models\Carts;
use ecommerce_core\models\Orders;
use ecommerce_core\models\Products;
use base_core\models\Users;
use base_core\models\VirtualUsers;

extract(Message::aliases());

Widgets::register('carts', function() use ($t) {
	$open = Carts::find('count', ['conditions' => ['status' => 'open']]);
	$expired = Carts::find('count', ['conditions' => ['status' => 'expired']]);

	return [
		'title' => $t('Carts'),
		'url' => [
			'controller' => 'Carts', 'action' => 'index', 'library' => 'ecommerce_core'
		],
		'data' => [
			$t('Open') => $open,
			$t('Expired') => $expired
		]
	];
}, [
	'type' => Widgets::TYPE_COUNTER,
	'group' => Widgets::GROUP_DASHBOARD,
]);

Widgets::register('users', function() use ($t) {
	$total = Users::find('count', ['conditions' => [
		'is_active' => true,
		'role' => ['customer', 'merchant']
	]]);
	$total += VirtualUsers::find('count', ['conditions' => [
		'is_active' => true,
		'role' => ['customer', 'merchant']
	]]);

	return [
		'data' => [
			$t('Customers') => $total
		]
	];
}, [
	'type' => Widgets::TYPE_COUNTER,
	'group' => Widgets::GROUP_DASHBOARD,
]);

Widgets::register('total_products', function() use ($t) {
	$products = Products::find('all', [
		'conditions' => [
			'is_published' => true
		]
	]);

	$stock = Products::find('first', [
		'fields' => [
			'SUM(stock - stock_reserved) as stock_virtual'
		]
	])->stock_virtual;

	return [
		'url' => [
			'controller' => 'ProductGroups', 'action' => 'index', 'library' => 'ecommerce_core'
		],
		'title' => $t('Products'),
		'data' => [
			$t('Total') => $products->count(),
			$t('In stock') => $stock
		]
	];
}, [
	'type' => Widgets::TYPE_COUNTER,
	'group' => Widgets::GROUP_DASHBOARD,
]);

Widgets::register('ecommerce_pending', function() use ($t) {
	$orders = Orders::find('count', [
		'conditions' => [
			'status NOT' => ['processed', 'cancelled', 'expired', 'checking-out']
		]
	]);
	$products = Products::find('count', [
		'conditions' => [
			'(stock - stock_reserved)' => ['<=' => 0]
		]
	]);
	return [
		'title' => $t('Pending'),
		'class' => $orders || $products ? 'negative' : 'positive',
		'url' => [
			'controller' => 'Orders', 'action' => 'index', 'library' => 'ecommerce_core'
		],
		'data' => [
			$t('Orders') => $orders,
			$t('Out of Stock') => $products
		]
	];
}, [
	'type' => Widgets::TYPE_COUNTER,
	'group' => Widgets::GROUP_DASHBOARD,
	'weight' => Widgets::WEIGHT_LOW
]);

?>