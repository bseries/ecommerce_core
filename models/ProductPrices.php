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

namespace ecommerce_core\models;

use Finance\Price;
use ecommerce_core\models\ProductPriceGroups;
use li3_access\security\Access;

class ProductPrices extends \base_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_product_prices'
	];

	protected static $_actsAs = [
		'base_core\extensions\data\behavior\Localizable' => [
			'fields' => [
				'price' => 'money'
			]
		]
	];

	public function group($entity) {
		return ProductPriceGroups::find('first', [
			'conditions' => [
				'id' => $entity->group
			]
		]);
	}

	// When we display net prices as gross to user which we don't have
	// any geographic and tax information of default to standard taxZone.
	public function price($entity, $taxZone) {
		return new Price($entity->price, $entity->price_currency, $entity->price_type, $taxZone);
	}

	public function hasAccess($entity, $user) {
		return Access::check('entity', $user, ['request' => $entity], [
			'rules' => $entity->group()->data('access')
		]) === [];
	}
}

?>