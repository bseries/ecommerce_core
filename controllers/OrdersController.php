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

namespace ecommerce_core\controllers;

use base_core\models\Users;
use billing_invoice\models\Invoices;
use ecommerce_core\models\Orders;
use ecommerce_core\models\Shipments;
use li3_flash_message\extensions\storage\FlashMessage;
use lithium\g11n\Message;

class OrdersController extends \base_core\controllers\BaseController {

	use \base_core\controllers\AdminIndexTrait;
	use \base_core\controllers\AdminAddTrait;
	use \base_core\controllers\AdminEditTrait;
	use \base_core\controllers\AdminUpdateStatusTrait;
	use \base_core\controllers\UsersTrait;

	public function admin_mark_processed() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::first($this->request->id);

		if ($item->save(['status' => 'processed'], ['whitelist' => ['status']])) {
			$model::pdo()->commit();
			FlashMessage::write($t('Successfully set status.', ['scope' => 'ecommerce_core']), [
				'level' => 'success'
			]);
		} else {
			$model::pdo()->rollback();
			FlashMessage::write($t('Failed to change status.', ['scope' => 'ecommerce_core']), [
				'level' => 'error'
			]);
		}
		return $this->redirect($this->request->referer());
	}

	protected function _selects($item = null) {
		$statuses = Orders::enum('status');
		$shipmentStatuses = Shipments::enum('status');
		$invoiceStatuses = Invoices::enum('status');

		if ($item) {
			$users = $this->_users($item, ['field' => 'user_id', 'empty' => true]);
		}

		return compact('statuses', 'invoiceStatuses', 'shipmentStatuses', 'users');
	}
}

?>