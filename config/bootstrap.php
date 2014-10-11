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

define('ECOMMERCE_CORE_VERSION', '1.2.0');

require 'panes.php';
require 'settings.php';
require 'media.php';
require 'widgets.php';
require 'jobs.php';

use li3_access\security\Access;
use base_tag\models\Tags;

$rules = Access::adapter('entity');

$rules->add('user.role:merchant', function($user, $entity, $options) {
	return $user->role == 'merchant';
});
$rules->add('user.role:customer', function($user, $entity, $options) {
	return $user->role == 'customer';
});

Tags::registerDependent('ecommerce_core\models\ProductGroups');

?>