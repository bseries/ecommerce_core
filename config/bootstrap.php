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

use cms_core\extensions\cms\Settings;
use cms_core\extensions\cms\Panes;
use lithium\g11n\Message;
use cms_media\models\Media;

extract(Message::aliases());

Panes::register('ecommerce_core', 'ecommerce', [
	'title' => $t('eCommerce'),
	'group' => Panes::GROUP_AUTHORING,
	'url' => $base = ['controller' => 'ecommerce', 'library' => 'ecommerce_core', 'admin' => true],
	'actions' => [
		$t('List orders') => ['controller' => 'Orders', 'action' => 'index'] + $base,
		// $t('New order') => ['controller' => 'Orders', 'action' => 'add'] + $base,
		$t('List products') => ['controller' => 'ProductGroups', 'action' => 'index'] + $base,
		$t('New product') => ['controller' => 'ProductGroups', 'action' => 'add'] + $base,
		// $t('List product variants') => ['controller' => 'Products', 'action' => 'index'] + $base,
		$t('New product variant') => ['controller' => 'Products', 'action' => 'add'] + $base,
		$t('List shipments') => ['controller' => 'Shipments', 'action' => 'index'] + $base,
		$t('List carts') => ['controller' => 'Carts', 'action' => 'index'] + $base,
	]
]);

// Number Format
//
// Parsed with sprintf.
// Parsed with strftime.
Settings::register('ecommerce_core', 'order.numberPattern.number', '%04.d');
Settings::register('ecommerce_core', 'order.numberPattern.prefix', '%Y');
Settings::register('ecommerce_core', 'paypal.email', 'billing@example.com');
Settings::register('ecommerce_core', 'checkout.expire', '+1 week');

Media::registerDependent('ecommerce_core\models\Products', [
	'cover' => 'direct',
	'media' => 'joined'
]);
Media::registerDependent('ecommerce_core\models\ProductGroups', [
	'cover' => 'direct',
	'media' => 'joined'
]);

?>