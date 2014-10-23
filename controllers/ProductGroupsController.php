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

use ecommerce_core\models\Products;
use ecommerce_core\models\ProductGroups;
use ecommerce_brand\models\Brands;
use li3_access\security\Access;
use lithium\core\Libraries;

class ProductGroupsController extends \base_core\controllers\BaseController {

	use \base_core\controllers\AdminAddTrait;
	use \base_core\controllers\AdminEditTrait;
	use \base_core\controllers\AdminDeleteTrait;

	use \base_core\controllers\AdminPublishTrait;
	use \base_core\controllers\AdminPromoteTrait;

	public function admin_index() {
		$data = ProductGroups::find('all', [
			'order' => ['created' => 'DESC']
		]);
		return compact('data') + $this->_selects();
	}

	protected function _redirectUrl($item = null) {
		if (!$item) {
			return [];
		}
		return ['#' => $item->id];
	}

	protected function _selects($item = null) {
		$data = array_keys(Access::adapter('entity')->get());
		$skip = ['allowAll', 'denyAll', 'allowAnyUser', 'allowIp'];
		$rules = [];

		foreach ($data as $item) {
			if (in_array($item, $skip)) {
				continue;
			}
			$rules[$item] = $item;
		}
		if (Libraries::get('ecommerce_brand')) {
			$brands = Brands::find('list');
		}
		return compact('rules', 'brands');
	}
}

?>