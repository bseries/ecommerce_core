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
use ecommerce_core\ecommerce\aquisition\Methods as AquisitionMethods;
use ecommerce_core\models\ProductAttributes;
use ecommerce_core\models\ProductGroups;
use ecommerce_core\models\Products;
use lithium\g11n\Message;

class ProductsController extends \base_core\controllers\BaseController {

	use \base_core\controllers\AdminIndexTrait;
	use \base_core\controllers\AdminAddTrait;
	use \base_core\controllers\AdminEditTrait;
	use \base_core\controllers\AdminDeleteTrait;

	use \base_core\controllers\AdminPublishTrait;

	protected function _selects($item = null) {
		extract(Message::aliases());

		$productGroups = ProductGroups::find('list');
		$aquisitionMethods = AquisitionMethods::enum();
		$currencies = Currencies::find('list');
		$attributeKeys = [];

		if ($item) {
			$attributeKeys = ProductAttributes::enum('key', [
				'size' => $t('size', ['scope' => 'ecommerce_core']),
				'color' => $t('color', ['scope' => 'ecommerce_core'])
			]);
		}
		return compact('productGroups', 'aquisitionMethods', 'currencies', 'attributeKeys');
	}
}

?>