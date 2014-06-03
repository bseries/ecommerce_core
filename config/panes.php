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

use cms_core\extensions\cms\Panes;
use lithium\g11n\Message;

extract(Message::aliases());

Panes::registerGroup('ecommerce_core', 'ecommerce', [
	'title' => $t('eCommerce'),
	'order' => 70
]);

$base = ['controller' => 'ecommerce', 'library' => 'ecommerce_core', 'admin' => true];
Panes::registerActions('ecommerce_core', 'ecommerce', [
	$t('List orders') => ['controller' => 'Orders', 'action' => 'index'] + $base,
	// $t('New order') => ['controller' => 'Orders', 'action' => 'add'] + $base,
	$t('List products groups') => ['controller' => 'ProductGroups', 'action' => 'index'] + $base,
	$t('New product group') => ['controller' => 'ProductGroups', 'action' => 'add'] + $base,
	$t('List products') => ['controller' => 'Products', 'action' => 'index'] + $base,
	$t('New product') => ['controller' => 'Products', 'action' => 'add'] + $base,
	$t('List shipments') => ['controller' => 'Shipments', 'action' => 'index'] + $base,
	$t('List carts') => ['controller' => 'Carts', 'action' => 'index'] + $base,
]);

?>