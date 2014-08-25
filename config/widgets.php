<?php
/**
 * Boutique Core
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
use lithium\storage\Cache;

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

Widgets::register('total_customers', function() use ($t) {
	$total = Users::find('count', ['conditions' => [
		'is_active' => true,
		'role' => ['customer', 'merchant']
	]]);
	$total += VirtualUsers::find('count', ['conditions' => [
		'is_active' => true,
		'role' => ['customer', 'merchant']
	]]);

	return [
		'title' => $t('Customers'),
		'url' => [
			'controller' => 'Users', 'action' => 'index', 'library' => 'cms_core'
		],
		'data' => [
			$total
		]
	];
}, [
	'type' => Widgets::TYPE_COUNTER,
	'group' => Widgets::GROUP_DASHBOARD,
]);

Widgets::register('total_orders_value', function() use ($t) {
	$orders = Orders::find('all', [
		'conditions' => [
			'status' => ['processed', 'checked-out', 'processing']
		],
		'fields' => [
			'id', 'user_id', 'virtual_user_id',
			'ecommerce_cart_id',
			'shipping_method',
			'payment_method'
		]
	]);
	$result = null;

	foreach ($orders as $item) {
		$cart = $item->cart(['fields' => ['id', 'modified']]);

		$cacheKey  = 'widget_total_orders_value_order_total_amount_';
		$cacheKey .= md5($item->modified . $cart->modified);

		if ($cached = Cache::read('default', $cacheKey)) {
			$value = $cached;
		} else {
			$user = $item->user();
			$value = $item->totalAmount($user, $cart, $user->taxZone());
			Cache::write('default', $cacheKey, $value, Cache::PERSIST);
		}

		if ($result) {
			$result = $result->add($value);
		} else {
			$result = $value;
		}
	}
	return [
		'title' => $t('Total Orders Value (net)'),
		'url' => [
			'controller' => 'Orders', 'action' => 'index', 'library' => 'ecommerce_core'
		],
		'class' => 'positive',
		'data' => [
			$result ? $result->getNet() : 0
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

	$stock = 0;
	foreach ($products as $product) {
		$stock += $product->stock();
	}

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
	'type' => Widgets::TYPE_COUNTER,
	'group' => Widgets::GROUP_DASHBOARD,
]);

?>