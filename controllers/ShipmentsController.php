<?php
/**
 * Bureau eCommerce
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace cms_ecommerce\controllers;

use cms_ecommerce\models\Shipments;
use cms_ecommerce\models\ShippingMethods;
use lithium\g11n\Message;
use li3_flash_message\extensions\storage\FlashMessage;

class ShipmentsController extends \cms_core\controllers\BaseController {

	use \cms_core\controllers\AdminAddTrait;
	use \cms_core\controllers\AdminEditTrait;
	use \cms_core\controllers\AdminDeleteTrait;

	public function admin_index() {
		$data = Shipments::find('all', [
			'order' => ['created' => 'DESC']
		]);
		return compact('data');
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

	protected function _selects($item) {
		$methods = [];

		$results = ShippingMethods::find('all');
		foreach ($results as $result) {
			$methods[$result->id] = $result->title;
		}
		return compact('methods');
	}
}

?>