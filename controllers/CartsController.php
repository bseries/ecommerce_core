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

use billing_core\models\TaxZones;
use ecommerce_core\models\Carts;
use lithium\core\Environment;
use lithium\g11n\Message;
use li3_flash_message\extensions\storage\FlashMessage;

class CartsController extends \base_core\controllers\BaseController {

	use \base_core\controllers\AdminIndexTrait;
	use \base_core\controllers\AdminEditTrait;
	use \base_core\controllers\AdminDeleteTrait;

	protected function _selects($item = null) {
		extract(Message::aliases());

		$statuses = Carts::enum('status', [
			'cancelled' => $t('cancelled'),
			'expired' => $t('expired'),
			'closed' => $t('closed'),
			'open' => $t('open')
		]);

		return compact('statuses');
	}

	public function admin_cancel() {
		extract(Message::aliases());

		Carts::pdo()->beginTransaction();

		$item = Carts::find('first', [
			'conditions' => ['id' => $this->request->id]
		]);
		if ($item->save(['status' => 'cancelled'], ['whitelist' => ['status']])) {
			Carts::pdo()->commit();
			FlashMessage::write($t('Successfully cancelled.'), ['level' => 'success']);
		} else {
			Carts::pdo()->rollback();
			FlashMessage::write($t('Failed to cancel.'), ['level' => 'error']);
		}
		return $this->redirect($this->request->referer());
	}
}

?>