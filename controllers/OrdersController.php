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

class OrdersController extends \cms_core\controllers\BaseController {

	use \cms_core\controllers\AdminAddTrait;
	use \cms_core\controllers\AdminEditTrait;
	use \cms_core\controllers\AdminDeleteTrait;

	public function admin_index() {
		$data = Orders::find('all', [
			'order' => ['number' => 'DESC']
		]);
		return compact('data');
	}

	protected function _selects($item) {
		$users = Users::find('list');

		$shipments = [];
		$results = Shipments::find('all');
		foreach ($results as $result) {
			$shipments[$result->id] = $result->method . '; ' . $result->address()->format('oneline');
		}

		$invoices = [];
		$results = Invoices::find('all');
		foreach ($results as $result) {
			$invoices[$result->id] = $result->number;
		}
		return compact('users', 'invoices', 'shipments');
	}
}

?>