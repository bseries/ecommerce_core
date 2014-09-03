<?php
/**
 * Boutique Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace ecommerce_core\controllers;

use cms_core\models\Currencies;
use ecommerce_core\models\Products;
use ecommerce_core\models\ProductAttributes;
use ecommerce_core\models\ProductGroups;
use lithium\g11n\Message;

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

	public function _selects($item = null) {
		extract(Message::aliases());

		$productGroups = ProductGroups::find('list');
		$currencies = Currencies::find('list');
		$attributeKeys = [];

		if ($item) {
			$attributeKeys = ProductAttributes::enum('key', [
				'size' => $t('size'),
				'label' => $t('label'),
				'make' => $t('make')
			]);
		}

		return compact('productGroups', 'currencies', 'attributeKeys');
	}
}

?>