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

use base_core\extensions\cms\Settings;
use base_core\extensions\cms\Jobs;
use ecommerce_core\models\Orders;
use ecommerce_core\models\Carts;
use ecommerce_core\models\Products;

Jobs::recur('ecommerce_core:expire', function() {
	Orders::expire();
	Carts::expire();
}, [
	'frequency' => Jobs::FREQUENCY_LOW
]);

if (Settings::read('stock.remote')) {
	Jobs::recur('ecommerce_core:stockRemote', function() {
		Products::syncWithRemoteStock();
	}, [
		'frequency' => Jobs::FREQUENCY_LOW,
		'depends' => [
			'ecommerce_lagerei:stock' => 'optional'
		]
	]);
}

?>
