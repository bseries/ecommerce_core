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

use lithium\g11n\Message;
use cms_core\extensions\cms\Widgets;
use ecommerce_core\models\Carts;
use ecommerce_core\models\Orders;
use ecommerce_core\models\Products;
use cms_core\models\Users;
use cms_core\models\VirtualUsers;

extract(Message::aliases());

Widgets::register('ecommerce_core', 'carts', function() use ($t) {
	$open = Carts::find('count', ['conditions' => ['status' => 'open']]);
	$expired = Carts::find('count', ['conditions' => ['status' => 'expired']]);

	return [
		'class' => null,
		'title' => false,
		'url' => [
			'controller' => 'Carts', 'action' => 'index', 'library' => 'ecommerce_core'
		],
		'data' => [
			$t('Open Carts') => $open,
			$t('Expired Carts') => $expired
		]
	];
}, [
	'type' => Widgets::TYPE_COUNT_MULTIPLE_BETA,
	'group' => Widgets::GROUP_DASHBOARD,
]);

Widgets::register('ecommerce_core', 'total_customers', function() use ($t) {
	$total = Users::find('count', ['conditions' => [
		'is_active' => true,
		'role' => ['customer', 'merchant']
	]]);
	$total += VirtualUsers::find('count', ['conditions' => [
		'is_active' => true,
		'role' => ['customer', 'merchant']
	]]);

	return [
		'class' => null,
		'url' => [
			'controller' => 'Users', 'action' => 'index', 'library' => 'cms_core'
		],
		'data' => [
			$t('Customers') => $total
		]
	];
}, [
	'type' => Widgets::TYPE_COUNT_MULTIPLE_BETA,
	'group' => Widgets::GROUP_DASHBOARD,
]);

Widgets::register('ecommerce_core', 'total_orders_value', function() use ($t) {
	$orders = Orders::find('all', [
		'conditions' => [
			'status' => 'processed'
		]
	]);
	$result = null;

	foreach ($orders as $item) {
		$value = $item->totalAmount($item->user(), $item->cart(), $item->user()->taxZone());

		if ($result) {
			$result = $result->add($value);
		} else {
			$result = $value;
		}
	}
	return [
		'class' => null,
		'title' => $t('Total Orders Value (net)'),
		'url' => [
			'controller' => 'Orders', 'action' => 'index', 'library' => 'ecommerce_core'
		],
		'class' => 'positive',
		'value' => $result ? $result->getNet() : 0
	];
}, [
	'type' => Widgets::TYPE_COUNT_SINGLE_ALPHA,
	'group' => Widgets::GROUP_DASHBOARD,
]);

Widgets::register('ecommerce_core', 'total_products', function() use ($t) {
	$products = Products::find('all', [
		'conditions' => [
			'is_published' => true
		]
	]);

	$stock = 0;
	foreach ($products as $product) {
		$stock += $product->stock();
	}

	return [
		'class' => null,
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
	'type' => Widgets::TYPE_COUNT_MULTIPLE_ALPHA,
	'group' => Widgets::GROUP_DASHBOARD,
]);

Widgets::register('ecommerce_core', 'ecommerce_pending', function() use ($t) {
	$orders = Orders::find('count', [
		'conditions' => [
			'status NOT' => ['processed', 'cancelled', 'expired']
		]
	]);
	$products = Products::find('all')->find(function($item) {
		return $item->stock() <= 0;
	})->count();

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
	'type' => Widgets::TYPE_COUNT_MULTIPLE_ALPHA,
	'group' => Widgets::GROUP_DASHBOARD,
]);

?>