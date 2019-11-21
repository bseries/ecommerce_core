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

use base_core\async\Jobs;
use ecommerce_core\models\Orders;
use ecommerce_core\models\Carts;

Jobs::recur('ecommerce_core:expire', function() {
	return Orders::expire() && Carts::expire();
}, [
	'frequency' => Jobs::FREQUENCY_LOW
]);

?>