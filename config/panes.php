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

Panes::register('ecommerce', [
	'title' => $t('eCommerce'),
	'order' => 70
]);

$base = ['controller' => 'ecommerce', 'library' => 'ecommerce_core', 'admin' => true];
Panes::register('ecommerce.orders', [
	'title' => $t('Orders'),
	'url' => ['controller' => 'Orders', 'action' => 'index'] + $base
]);
Panes::register('ecommerce.productGroups', [
	'title' => $t('Product Groups'),
	'url' => ['controller' => 'ProductGroups', 'action' => 'index'] + $base
]);
Panes::register('ecommerce.products', [
	'title' => $t('Products'),
	'url' => ['controller' => 'Products', 'action' => 'index'] + $base
]);
Panes::register('ecommerce.shipments', [
	'title' => $t('Shipments'),
	'url' => ['controller' => 'Shipments', 'action' => 'index'] + $base
]);
Panes::register('ecommerce.carts', [
	'title' => $t('Carts'),
	'url' => ['controller' => 'Carts', 'action' => 'index'] + $base
]);

?>