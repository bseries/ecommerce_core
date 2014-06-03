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

use ecommerce_core\models\Shipments;
use ecommerce_core\models\ShippingMethods;
use lithium\g11n\Message;
use li3_flash_message\extensions\storage\FlashMessage;

class ShipmentsController extends \cms_core\controllers\BaseController {

	use \cms_core\controllers\AdminAddTrait;
	use \cms_core\controllers\AdminEditTrait;

	public function admin_index() {
		$data = Shipments::find('all', [
			'order' => ['created' => 'DESC']
		]);
		return compact('data') + $this->_selects();
	}

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
			FlashMessage::write($t('Status changed.'), ['level' => 'success']);
		} else {
			FlashMessage::write($t('Failed to change status.'), ['level' => 'error']);
		}
		return $this->redirect($this->request->referer());
	}

	protected function _selects($item = null) {
		extract(Message::aliases());

		$statuses = Shipments::enum('status', [
			'created' => $t('created'),
			'cancelled' => $t('cancelled'),
			'shipping-scheduled' => $t('shipping scheduled'),
			'shipping-error' => $t('shipping error'),
			'shipping' => $t('shipping'),
			'shipped' => $t('shipped'),
			'delivered' => $t('delivered')
		]);
		$methods = ShippingMethods::find('list');

		return compact('methods', 'statuses');
	}
}

?>