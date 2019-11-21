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

namespace ecommerce_core\models;

class PaymentMethods extends \base_core\models\Base {

	public static function register($name, array $data = []) {
		trigger_error('PaymentMethods has been moved into billing_payment.', E_USER_DEPRECATED);
		return parent::register($name, $data);
	}

	public static function find($type, array $options = []) {
		trigger_error('PaymentMethods has been moved into billing_payment.', E_USER_DEPRECATED);
		return parent::find($type, $options);
	}
}

?>