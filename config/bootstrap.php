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
use cms_ecommerce\models\ShippingMethods;
use cms_ecommerce\models\PaymentMethods;
use cms_ecommerce\models\ProductPriceGroups;

extract(Message::aliases());

Panes::register('cms_ecommerce', 'ecommerce', [
	'title' => $t('eCommerce'),
	'group' => Panes::GROUP_AUTHORING,
	'url' => $base = ['controller' => 'ecommerce', 'library' => 'cms_ecommerce', 'admin' => true],
	'actions' => [
		$t('List carts') => ['controller' => 'Carts', 'action' => 'index'] + $base,
		$t('List orders') => ['controller' => 'Orders', 'action' => 'index'] + $base,
		$t('New order') => ['controller' => 'Orders', 'action' => 'add'] + $base,
		$t('List products') => ['controller' => 'ProductGroups', 'action' => 'index'] + $base,
		$t('New product') => ['controller' => 'ProductGroups', 'action' => 'add'] + $base,
		// $t('List product variants') => ['controller' => 'Products', 'action' => 'index'] + $base,
		$t('New product variant') => ['controller' => 'Products', 'action' => 'add'] + $base,
		// $t('List shipments') => ['controller' => 'Shipments', 'action' => 'index'] + $base,
	]
]);

// Number Format
//
// Parsed with sprintf.
// Parsed with strftime.
Settings::register('cms_ecommerce', 'orderNumberPattern.number', '%04.d');
Settings::register('cms_ecommerce', 'orderNumberPattern.prefix', '%Y');

Media::registerDependent('cms_ecommerce\models\Products', [
	'cover' => 'direct',
	'media' => 'joined'
]);
Media::registerDependent('cms_ecommerce\models\ProductGroups', [
	'cover' => 'direct',
	'media' => 'joined'
]);

PaymentMethods::register('invoice', [
	'title' => $t('Invoice')
]);
PaymentMethods::register('paypal', [
	'title' => $t('Paypal')
]);
PaymentMethods::register('prepayment', [
	'title' => $t('Prepayment')
]);

use SebastianBergmann\Money\Money;
use SebastianBergmann\Money\Currency;

ShippingMethods::register('default', [
	'title' => $t('Default Shipping'),
	'price_eur' => function($user, $cart, $type, $taxZone, $currency) {
		$currency = new Currency($currency);

		$free = new Money(5000, $currency); // gross

		if ($cart->totalAmount($user, 'gross', $taxZone, (string) $currency)->greaterThan($free)) {
			$result = new Money(0, $currency);
		} elseif ($user->role === 'merchant') {
			$result = new Money(490, $currency); // gross
		} else {
			$result = new Money(390, $currency); // gross
		}
		if ($type === 'gross') {
			return $result;
		}
		return $result->subtract($result->multiply(($taxZone->rate / 100)));
	}
]);

ProductPriceGroups::register('merchant', [
	'title' => 'Merchant',
	'legible' => function($user) {
		if ($user->role == 'admin') {
			return true;
		}
		return $user->role == 'merchant';
	}
]);
ProductPriceGroups::register('customer', [
	'title' => 'Customer',
	'legible' => function($user) {
		if ($user->role == 'admin') {
			return true;
		}
		return $user->role == 'customer';
	}
]);

?>