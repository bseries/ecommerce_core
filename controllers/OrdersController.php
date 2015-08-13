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

namespace ecommerce_core\controllers;

use base_core\models\Users;
use ecommerce_core\models\Orders;
use billing_invoice\models\Invoices;
use ecommerce_core\models\Shipments;

class OrdersController extends \base_core\controllers\BaseController {

	use \base_core\controllers\AdminIndexTrait;
	use \base_core\controllers\AdminAddTrait;
	use \base_core\controllers\AdminEditTrait;
	use \base_core\controllers\AdminUpdateStatusTrait;

	protected function _selects($item = null) {
		$statuses = Orders::enum('status');
		$shipmentStatuses = Shipments::enum('status');
		$invoiceStatuses = Invoices::enum('status');

		return compact('statuses', 'invoiceStatuses', 'shipmentStatuses');
	}
}

?>