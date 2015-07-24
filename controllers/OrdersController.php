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

namespace ecommerce_core\controllers;

use base_core\models\Users;
use ecommerce_core\models\Orders;
use billing_invoice\models\Invoices;
use ecommerce_core\models\Shipments;
use lithium\g11n\Message;

class OrdersController extends \base_core\controllers\BaseController {

	use \base_core\controllers\AdminIndexTrait;
	use \base_core\controllers\AdminAddTrait;
	use \base_core\controllers\AdminEditTrait;
	use \base_core\controllers\AdminUpdateStatusTrait;

	protected function _selects($item = null) {
		extract(Message::aliases());

		$statuses = Orders::enum('status', [
			'refunding' => $t('refunding', ['scope' => 'ecommerce_core']),
			'on-backorder' => $t('on backorder', ['scope' => 'ecommerce_core']),
			'cancelled' => $t('cancelled', ['scope' => 'ecommerce_core']),
			'processed' => $t('processed', ['scope' => 'ecommerce_core']),
			'processing' => $t('processing', ['scope' => 'ecommerce_core']),
			'checking-out' => $t('checking out', ['scope' => 'ecommerce_core']),
			'checked-out'=> $t('checked out', ['scope' => 'ecommerce_core']),
			'expired' => $t('expired', ['scope' => 'ecommerce_core'])
		]);
		$shipmentStatuses = Shipments::enum('status', [
			'created' => $t('created', ['scope' => 'ecommerce_core']),
			'cancelled' => $t('cancelled', ['scope' => 'ecommerce_core']),
			'shipping-scheduled' => $t('shipping scheduled', ['scope' => 'ecommerce_core']),
			'shipping' => $t('shipping', ['scope' => 'ecommerce_core']),
			'shipped' => $t('shipped', ['scope' => 'ecommerce_core']),
			'shipping-error' => $t('shipping error', ['scope' => 'ecommerce_core']),
		]);
		$invoiceStatuses = Invoices::enum('status', [
			'created' => $t('created', ['scope' => 'ecommerce_core']), // open
			'sent' => $t('sent', ['scope' => 'ecommerce_core']), // open
			'paid' => $t('paid', ['scope' => 'ecommerce_core']),  // paid
			'cancelled' => $t('cancelled', ['scope' => 'ecommerce_core']), // storno

			'awaiting-payment' => $t('awaiting payment', ['scope' => 'ecommerce_core']),
			'payment-accepted' => $t('payment accepted', ['scope' => 'ecommerce_core']),
			'payment-remotely-accepted' => $t('payment remotely accepted', ['scope' => 'ecommerce_core']),
			'payment-error' => $t('payment error', ['scope' => 'ecommerce_core']),
		]);

		return compact('statuses', 'invoiceStatuses', 'shipmentStatuses');
	}
}

?>