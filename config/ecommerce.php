<?php
/**
 * eCommerce Core
 *
 * Copyright (c) 2014 David Persson - All rights reserved.
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace ecommerce_core\config;

use base_core\extensions\cms\Settings;

if (Settings::read('contact.primary')) {
	Settings::register('contact.shipping', Settings::read('contact.primary'));
} else {
	trigger_error('No primary contact found, using deprecated default.', E_USER_DEPRECATED);
	Settings::register('contact.shipping', Settings::read('contact.default'));
}

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

Settings::register('order.sendCheckedOutMail', false);

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