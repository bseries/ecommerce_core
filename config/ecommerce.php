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

use base_core\extensions\cms\Settings;

Settings::register('contact.shipping', Settings::read('contact.default'));

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

// strtotime()-compatible string with time in future, when a cart
// should expire.
Settings::register('checkout.expire', '+1 week');

// Allows to limit the maximum number of items of onte product
// that can be put into cart. Use `false` to disable check.
Settings::register('cart.limitItemsPerPosition', false);

Settings::register('ecommerce.shipmentTerms', null);
Settings::register('shipment.sendShippedMail', false);

// Enable if we should actually check if there is stock
// available when user takes product.
Settings::register('stock.check', true);

// Enables automatic tagging of entities, once saved.
Settings::register('productGroup.useAutoTagging', false);

?>