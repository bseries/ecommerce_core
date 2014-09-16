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

use base_core\extensions\cms\Features;
use base_core\extensions\cms\Settings;

// Number Format
Settings::register('order.number', [
	'extract' => '/[0-9]{4}([0-9]{4})/',
	'generate' => '%Y%%04.d'
]);
Settings::register('shipment.number', [
	'extract' => '/[0-9]{4}([0-9]{4})/',
	'generate' => '%Y%%04.d'
]);
Settings::register('product.number', [
	'extract' => '/[0-9]{4}([0-9]{4})/',
	'generate' => '%Y%%04.d'
]);

Settings::register('service.paypal.default.email', 'billing@example.com');
Settings::register('checkout.expire', '+1 week');
Settings::register('cart.limitItemsPerPosition', false); // false to disable check

Settings::register('ecommerce.shipmentTerms', null);
Features::register('shipment.sendShippedMail', false);

Features::register('stock.check', true);

?>