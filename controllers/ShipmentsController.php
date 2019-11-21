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

namespace ecommerce_core\controllers;

use billing_core\models\Currencies;
use ecommerce_core\models\Shipments;
use ecommerce_core\ecommerce\shipping\Methods as ShippingMethods;
use lithium\g11n\Message;
use li3_flash_message\extensions\storage\FlashMessage;

class ShipmentsController extends \base_core\controllers\BaseController {

	use \base_core\controllers\AdminIndexTrait;
	use \base_core\controllers\AdminAddTrait;
	use \base_core\controllers\AdminEditTrait;
	use \base_core\controllers\DownloadTrait;
	use \base_core\controllers\UsersTrait;

	public function admin_export_pdf() {
		extract(Message::aliases());

		$item = Shipments::find('first', [
			'conditions' => [
				'id' => $this->request->id
			]
		]);

		$this->_renderDownload(
			$stream = $item->exportAsPdf(),
			'application/pdf'
		);
		fclose($stream);
	}

	protected function _selects($item = null) {
		if ($item) {
			$statuses = Shipments::enum('status');
			$methods = ShippingMethods::enum();
			$currencies = Currencies::find('list');
			$users = $this->_users($item, ['field' => 'user_id', 'empty' => true]);

			return compact('methods', 'statuses', 'currencies', 'users');
		}
		return [];
	}
}

?>
