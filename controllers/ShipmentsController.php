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

use billing_core\models\Currencies;
use ecommerce_core\models\Shipments;
use ecommerce_core\models\ShippingMethods;
use base_core\models\VirtualUsers;
use base_core\models\Users;
use lithium\g11n\Message;
use li3_flash_message\extensions\storage\FlashMessage;

class ShipmentsController extends \base_core\controllers\BaseController {

	use \base_core\controllers\AdminIndexTrait;
	use \base_core\controllers\AdminAddTrait;
	use \base_core\controllers\AdminEditTrait;

	public function admin_ship() {
		extract(Message::aliases());

		$model = $this->_model;

		$item = $model::first($this->request->id);
		$status = $item->method()->delegate ? 'shipping-scheduled' : 'shipped';

		$result = $item->save(
			['status' => $status],
			['whitelist' => ['status'], 'validate' => false]
		);
		if ($result) {
			FlashMessage::write($t('Status changed.', ['scope' => 'ecommerce_core']), [
				'level' => 'success'
			]);
		} else {
			FlashMessage::write($t('Failed to change status.', ['scope' => 'ecommerce_core']), [
				'level' => 'error'
			]);
		}
		return $this->redirect($this->request->referer());
	}

	protected function _selects($item = null) {
		extract(Message::aliases());

		$statuses = Shipments::enum('status', [
			'created' => $t('created', ['scope' => 'ecommerce_core']),
			'cancelled' => $t('cancelled', ['scope' => 'ecommerce_core']),
			'shipping-scheduled' => $t('shipping scheduled', ['scope' => 'ecommerce_core']),
			'shipping' => $t('shipping', ['scope' => 'ecommerce_core']),
			'shipped' => $t('shipped', ['scope' => 'ecommerce_core']),
		]);
		$methods = ShippingMethods::find('list');
		$currencies = Currencies::find('list');
		$virtualUsers = [null => '-'] + VirtualUsers::find('list', ['order' => 'name']);
		$users = [null => '-'] + Users::find('list', ['order' => 'name']);

		return compact('methods', 'statuses', 'currencies', 'users', 'virtualUsers');
	}
}

?>