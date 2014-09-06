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

use base_core\models\Currencies;
use ecommerce_core\models\Products;
use ecommerce_core\models\ProductAttributes;
use ecommerce_core\models\ProductGroups;
use ecommerce_brand\models\Brands;
use lithium\g11n\Message;
use lithium\core\Libraries;

class ProductsController extends \base_core\controllers\BaseController {

	protected $_redirectUrl = ['controller' => 'ProductGroups'];

	use \base_core\controllers\AdminAddTrait;
	use \base_core\controllers\AdminEditTrait;
	use \base_core\controllers\AdminDeleteTrait;

	use \base_core\controllers\AdminPublishTrait;

	public function admin_index() {
		$data = Products::find('all', [
			'order' => ['id' => 'ASC']
		]);
		return compact('data');
	}

	public function _selects($item = null) {
		extract(Message::aliases());

		$productGroups = ProductGroups::find('list');
		$currencies = Currencies::find('list');
		$attributeKeys = [];

		if ($item) {
			$attributeKeys = ProductAttributes::enum('key', [
				'size' => $t('size'),
				'color' => $t('color')
			]);
		}
		if (Libraries::get('ecommerce_brand')) {
			$brands = Brands::find('list');
		}
		return compact('productGroups', 'currencies', 'attributeKeys', 'brands');
	}
}

?>