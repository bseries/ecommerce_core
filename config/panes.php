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

use base_core\extensions\cms\Panes;
use lithium\g11n\Message;

extract(Message::aliases());

Panes::register('ecommerce', [
	'title' => $t('Boutique', ['scope' => 'ecommerce_core']),
	'weight' => 30
]);

$base = ['controller' => 'ecommerce', 'library' => 'ecommerce_core', 'admin' => true];
Panes::register('ecommerce.orders', [
	'title' => $t('Orders', ['scope' => 'ecommerce_core']),
	'url' => ['controller' => 'Orders', 'action' => 'index'] + $base,
	'weight' => 0
]);
Panes::register('ecommerce.carts', [
	'title' => $t('Carts', ['scope' => 'ecommerce_core']),
	'url' => ['controller' => 'Carts', 'action' => 'index'] + $base,
	'weight' => 1
]);
Panes::register('ecommerce.shipments', [
	'title' => $t('Shipments', ['scope' => 'ecommerce_core']),
	'url' => ['controller' => 'Shipments', 'action' => 'index'] + $base,
	'weight' => 2
]);

Panes::register('ecommerce.products', [
	'title' => $t('Products', ['scope' => 'ecommerce_core']),
	'url' => ['controller' => 'Products', 'action' => 'index'] + $base,
	'weight' => 10
]);
Panes::register('ecommerce.productGroups', [
	'title' => $t('Product Groups', ['scope' => 'ecommerce_core']),
	'url' => ['controller' => 'ProductGroups', 'action' => 'index'] + $base,
	'weight' => 11
]);

?>