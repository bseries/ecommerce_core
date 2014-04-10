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
use cms_core\models\Users;
use cms_core\models\VirtualUsers;

extract(Message::aliases());

Widgets::register('ecommerce_core', 'carts', [
	'type' => Widgets::TYPE_COUNT_MULTIPLE_BETA,
	'group' => Widgets::GROUP_DASHBOARD,
	'url' => [
		'controller' => 'Carts', 'action' => 'index', 'library' => 'ecommerce_core'
	],
	'data' => function() use ($t) {
		$open = Carts::find('count', ['conditions' => ['status' => 'open']]);
		$expired = Carts::find('count', ['conditions' => ['status' => 'expired']]);

		return [
			'title' => false,
			'data' => [
				$t('Open Carts') => $open,
				$t('Expired Carts') => $expired
			]
		];
	}
]);

Widgets::register('ecommerce_core', 'total_customers', [
	'type' => Widgets::TYPE_COUNT_MULTIPLE_BETA,
	'group' => Widgets::GROUP_DASHBOARD,
	'url' => [
		'controller' => 'Users', 'action' => 'index', 'library' => 'cms_core'
	],
	'data' => function() use ($t) {
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
	}
]);

Widgets::register('ecommerce_core', 'total_orders_value', [
	'type' => Widgets::TYPE_COUNT_SINGLE_ALPHA,
	'group' => Widgets::GROUP_DASHBOARD,
	'url' => [
		'controller' => 'Orders', 'action' => 'index', 'library' => 'ecommerce_core'
	],
	'data' => function() use ($t) {
		$orders = Orders::find('all');
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
			'title' => $t('Total Orders Value (net)'),
			'value' => $value->getNet()
		];
	}
]);

?>