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

namespace ecommerce_core\models;

class PaymentMethods extends \billing_payment\models\PaymentMethods {

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