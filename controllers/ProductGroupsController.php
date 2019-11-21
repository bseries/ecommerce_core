<?php
/**
 * eCommerce Core
 *
 * Copyright (c) 2014 David Persson - All rights reserved.
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
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