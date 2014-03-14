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

use cms_ecommerce\models\Products;
use cms_ecommerce\models\ProductGroups;

class ProductsController extends \cms_core\controllers\BaseController {

	protected $_redirectUrl = ['controller' => 'ProductGroups'];

	use \cms_core\controllers\AdminAddTrait;
	use \cms_core\controllers\AdminEditTrait;
	use \cms_core\controllers\AdminDeleteTrait;

	use \cms_core\controllers\AdminPublishTrait;

	public function admin_index() {
		$data = Products::find('all', [
			'order' => ['id' => 'ASC']
		]);
		return compact('data');
	}

	public function _selects($item) {
		$productGroups = [];
		$results = ProductGroups::find('all');

		foreach ($results as $result) {
			$productGroups[$result->id] = $result->title;
		}
		$currencies = [
			'EUR' => 'EUR',
			'USD' => 'USD'
		];

		return compact('productGroups', 'currencies');
	}
}

?>