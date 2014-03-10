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

use lithium\net\http\Router;

$persist = ['persist' => ['admin', 'controller']];

Router::connect('/admin/ecommerce/orders/{:id:[0-9]+}', [
	'controller' => 'orders', 'library' => 'cms_ecommerce', 'action' => 'view', 'admin' => true
], $persist);
Router::connect('/admin/ecommerce/orders/{:action}', [
	'controller' => 'orders', 'library' => 'cms_ecommerce', 'admin' => true
], $persist);
Router::connect('/admin/ecommerce/orders/{:action}/{:id:[0-9]+}', [
	'controller' => 'orders', 'library' => 'cms_ecommerce', 'admin' => true
], $persist);

Router::connect('/admin/ecommerce/carts/{:id:[0-9]+}', [
	'controller' => 'carts', 'library' => 'cms_ecommerce', 'action' => 'view', 'admin' => true
], $persist);
Router::connect('/admin/ecommerce/carts/{:action}', [
	'controller' => 'carts', 'library' => 'cms_ecommerce', 'admin' => true
], $persist);
Router::connect('/admin/ecommerce/carts/{:action}/{:id:[0-9]+}', [
	'controller' => 'carts', 'library' => 'cms_ecommerce', 'admin' => true
], $persist);

Router::connect('/admin/ecommerce/products/{:id:[0-9]+}', [
	'controller' => 'products', 'library' => 'cms_ecommerce', 'action' => 'view', 'admin' => true
], $persist);
Router::connect('/admin/ecommerce/products/{:action}', [
	'controller' => 'products', 'library' => 'cms_ecommerce', 'admin' => true
], $persist);
Router::connect('/admin/ecommerce/products/{:action}/{:id:[0-9]+}', [
	'controller' => 'products', 'library' => 'cms_ecommerce', 'admin' => true
], $persist);

Router::connect('/admin/ecommerce/{:id:[0-9]+}', [
	'controller' => 'ecommerce', 'library' => 'cms_ecommerce', 'action' => 'view', 'admin' => true
], $persist);
Router::connect('/admin/ecommerce/{:action}', [
	'controller' => 'ecommerce', 'library' => 'cms_ecommerce', 'admin' => true
], $persist);
Router::connect('/admin/ecommerce/{:action}/{:id:[0-9]+}', [
	'controller' => 'ecommerce', 'library' => 'cms_ecommerce', 'admin' => true
], $persist);


?>