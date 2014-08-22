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

define('ECOMMERCE_CORE_VERSION', '1.2.0');

require 'panes.php';
require 'settings.php';
require 'media.php';
require 'widgets.php';

use cms_core\extensions\cms\Jobs;
use ecommerce_core\models\Orders;
use ecommerce_core\models\Carts;
use li3_access\security\Access;

Jobs::recur('ecommerce_core', 'expire', function() {
	Orders::expire();
	Carts::expire();
}, [
	'frequency' => Jobs::FREQUENCY_LOW
]);

$rules = Access::adapter('entity');

$rules->add('user.role:merchant', function($user, $entity, $options) {
	return $user->role == 'merchant';
});
$rules->add('user.role:customer', function($user, $entity, $options) {
	return $user->role == 'customer';
});

?>