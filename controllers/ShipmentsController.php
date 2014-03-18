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