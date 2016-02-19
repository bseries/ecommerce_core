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

use lithium\g11n\Message;
use base_core\extensions\cms\Widgets;
use ecommerce_core\models\Carts;
use ecommerce_core\models\Orders;
use ecommerce_core\models\Products;
use base_core\models\Users;

extract(Message::aliases());

Widgets::register('carts', function() use ($t) {
	$open = Carts::find('count', ['conditions' => ['status' => 'open']]);
	$expired = Carts::find('count', ['conditions' => ['status' => 'expired']]);

	return [
		'title' => $t('Carts', ['scope' => 'ecommerce_core']),
		'url' => [
			'controller' => 'Carts', 'action' => 'index', 'library' => 'ecommerce_core'
		],
		'data' => [
			$t('Open', ['scope' => 'ecommerce_core']) => $open,
			$t('Expired', ['scope' => 'ecommerce_core']) => $expired
		]
	];
}, [
	'type' => Widgets::TYPE_COUNTER,
	'group' => Widgets::GROUP_DASHBOARD,
]);

Widgets::register('ecommerce_orders', function() use ($t) {
	$pending = Orders::find('count', [
		'conditions' => [
			'status NOT' => ['processed', 'cancelled', 'expired', 'checking-out']
		]
	]);
	$total = Orders::find('count');

	return [
		'title' => $t('Orders', ['scope' => 'ecommerce_core']),
		'class' => $pending ? 'negative' : 'positive',
		'url' => [
			'controller' => 'Orders', 'action' => 'index', 'library' => 'ecommerce_core'
		],
		'data' => [
			$t('Total', ['scope' => 'ecommerce_core']) => $total,
			$t('Open', ['scope' => 'ecommerce_core']) => $pending,
		]
	];
}, [
	'type' => Widgets::TYPE_COUNTER,
	'group' => Widgets::GROUP_DASHBOARD,
	'weight' => Widgets::WEIGHT_LOW
]);

Widgets::register('ecommerce_products', function() use ($t) {
	$total = Products::find('count');

	$outOfStock = Products::find('count', [
		'conditions' => [
			'(stock - stock_reserved)' => ['<=' => 0]
		]
	]);
	return [
		'title' => $t('Products', ['scope' => 'ecommerce_core']),
		'class' => $outOfStock ? 'negative' : 'positive',
		'url' => [
			'controller' => 'Products', 'action' => 'index', 'library' => 'ecommerce_core'
		],
		'data' => [
			$t('Total', ['scope' => 'ecommerce_core']) => $total,
			$t('Out of Stock', ['scope' => 'ecommerce_core']) => $outOfStock
		]
	];
}, [
	'type' => Widgets::TYPE_COUNTER,
	'group' => Widgets::GROUP_DASHBOARD,
	'weight' => Widgets::WEIGHT_LOW
]);

?>