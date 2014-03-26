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

use cms_billing\models\TaxZones;
use ecommerce_core\models\Carts;
use lithium\core\Environment;

class CartsController extends \cms_core\controllers\BaseController {

	use \cms_core\controllers\AdminEditTrait;
	use \cms_core\controllers\AdminDeleteTrait;

	public function admin_index() {
		$data = Carts::find('all', [
			'order' => ['created' => 'desc']
		]);
		return compact('data');
	}
}

?>