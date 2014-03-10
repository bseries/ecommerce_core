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

use cms_core\models\Users;
use cms_ecommerce\models\Orders;
use cms_billing\models\Invoices;

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

	protected function _selects() {
		$parent = parent::_selects();
		$users = Users::find('list');

		$shipments = [];

		$invoices = [];
		$results = Invoices::find('all');
		foreach ($results as $result) {
			$invoices[$result->id] = $result->number;
		}
		return compact('users', 'invoices', 'shipments') + $parent;
	}
}

?>