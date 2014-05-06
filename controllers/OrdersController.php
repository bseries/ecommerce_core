<?php
/**
 * Magasin Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace ecommerce_core\controllers;

use cms_core\models\Users;
use ecommerce_core\models\Orders;
use cms_billing\models\Invoices;
use ecommerce_core\models\Shipments;
use lithium\g11n\Message;

class OrdersController extends \cms_core\controllers\BaseController {

	use \cms_core\controllers\AdminAddTrait;
	use \cms_core\controllers\AdminEditTrait;
	use \cms_core\controllers\AdminUpdateStatusTrait;

	public function admin_index() {
		$data = Orders::find('all', [
			'order' => ['number' => 'DESC']
		]);
		return compact('data');
	}

	protected function _selects($item) {
		extract(Message::aliases());

		$statuses = Orders::enum('status', [
			'refunding' => $t('refunding'),
			'on-backorder' => $t('on backorder'),
			'cancelled' => $t('cancelled'),
			'processed' => $t('processed'),
			'processing' => $t('processing'),
			'checking-out' => $t('checking out'),
			'checked-out'=> $t('checked out'),
			'expired' => $t('expired')
		]);
		$shipmentStatuses = Shipments::enum('status', [
			'created' => $t('created'),
			'shipping-scheduled' => $t('shipping scheduled'),
			'shipping-error' => $t('shipping error'),
			'shipping' => $t('shipping'),
			'shipped' => $t('shipped'),
			'delivered' => $t('delivered')
		]);
		$invoiceStatuses = Invoices::enum('status', [
			'created' => $t('created'), // open
			'sent' => $t('sent'), // open
			'paid' => $t('paid'),  // paid
			'cancelled' => $t('cancelled'), // storno

			'awaiting-payment' => $t('awaiting payment'),
			'payment-accepted' => $t('payment accepted'),
			'payment-remotely-accepted' => $t('payment remotely accepted'),
			'payment-error' => $t('payment error'),
		]);

		return compact('statuses', 'invoiceStatuses', 'shipmentStatuses');
	}
}

?>