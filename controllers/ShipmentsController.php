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

use billing_core\models\Currencies;
use ecommerce_core\models\Shipments;
use ecommerce_core\models\ShippingMethods;
use base_core\models\Users;
use lithium\g11n\Message;
use li3_flash_message\extensions\storage\FlashMessage;

class ShipmentsController extends \base_core\controllers\BaseController {

	use \base_core\controllers\AdminIndexTrait;
	use \base_core\controllers\AdminAddTrait;
	use \base_core\controllers\AdminEditTrait;
	use \base_core\controllers\DownloadTrait;

	public function admin_export_pdf() {
		extract(Message::aliases());

		$item = Shipments::find('first', [
			'conditions' => [
				'id' => $this->request->id
			]
		]);
		$stream = $item->exportAsPdf();

		$this->_renderDownload(
			$this->_downloadBasename(
				null,
				'shipment',
				$item->number . '.pdf'
			),
			$stream
		);
		fclose($stream);
	}

	protected function _selects($item = null) {
		$statuses = Shipments::enum('status');
		$methods = ShippingMethods::find('list');
		$currencies = Currencies::find('list');
		$users = [null => '-'] + Users::find('list', ['order' => 'name']);

		return compact('methods', 'statuses', 'currencies', 'users');
	}
}

?>