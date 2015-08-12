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

use ecommerce_core\models\Products;
use ecommerce_core\models\ProductGroups;
use ecommerce_brand\models\Brands;
use li3_access\security\Access;
use lithium\core\Libraries;

class ProductGroupsController extends \base_core\controllers\BaseController {

	use \base_core\controllers\AdminIndexTrait;
	use \base_core\controllers\AdminAddTrait;
	use \base_core\controllers\AdminEditTrait;
	use \base_core\controllers\AdminDeleteTrait;

	use \base_core\controllers\AdminPublishTrait;
	use \base_core\controllers\AdminPromoteTrait;

	protected function _selects($item = null) {
		$rules = array_combine(
			$keys = array_keys(Access::adapter('entity')->get()),
			$keys
		);
		if (Libraries::get('ecommerce_brand')) {
			$brands = Brands::find('list');
		}
		return compact('rules', 'brands');
	}
}

?>