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
 * License. If not, see https://atelierdisko.de/licenses.
 */

use base_core\async\Jobs;
use ecommerce_core\models\Orders;
use ecommerce_core\models\Carts;

Jobs::recur('ecommerce_core:expire', function() {
	return Orders::expire() && Carts::expire();
}, [
	'frequency' => Jobs::FREQUENCY_LOW
]);

?>