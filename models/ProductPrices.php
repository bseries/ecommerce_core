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

use Exception;
use AD\Finance\Price;
use billing_core\models\ClientGroups;
use li3_access\security\Access;

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
		return ClientGroups::find('first', [
			'conditions' => [
				'id' => $entity->group
			]
		]);
	}

	public function amount($entity) {
		return new Price(
			(integer) $entity->amount,
			$entity->amount_currency,
			$entity->amount_type,
			(integer) $entity->tax_rate
		);
	}

	public function hasAccess($entity, $user) {
		return Access::check('entity', $user, ['request' => $entity], [
			'rules' => $entity->group()->data('access')
		]) === [];
	}

	// @deprecated
	public function taxType($entity) {
		throw new Exception('Removed.');
	}
}

?>