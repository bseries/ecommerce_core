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
use billing_core\models\TaxTypes;

class ProductPrices extends \base_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_product_prices'
	];

	protected static $_actsAs = [
		'base_core\extensions\data\behavior\Localizable' => [
			'fields' => [
				'amount' => 'money'
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

	// Prices may be retrieved using a temporary user.
	// That user must at a minimum have tax country and vat_reg_no fields set.
	public function amount($entity) {
		return new Price(
			$entity->amount,
			$entity->amount_currency,
			$entity->amount_type,
			$entity->tax_rate
		);
	}

	public function taxType($entity) {
		return TaxTypes::find('first', ['conditions' => ['id' => $entity->tax_type]]);
	}

	public function hasAccess($entity, $user) {
		return Access::check('entity', $user, ['request' => $entity], [
			'rules' => $entity->group()->data('access')
		]) === [];
	}
}

?>