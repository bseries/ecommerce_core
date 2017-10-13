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

namespace ecommerce_core\models;

use AD\Finance\Price;
use Exception;
use billing_core\billing\ClientGroups;
use billing_core\billing\TaxTypes;
use ecommerce_core\ecommerce\aquisition\Methods as AquisitionMethods;

class ProductPrices extends \base_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_product_prices'
	];

	public $belongsTo = [
		'Product' => [
			'to' => 'ecommerce_core\models\Products',
			'key' => 'ecommerce_product_id'
		]
	];

	protected $_actsAs = [
		'base_core\extensions\data\behavior\RelationsPlus',
		'base_core\extensions\data\behavior\Localizable' => [
			'fields' => [
				'amount' => 'money'
			]
		]
	];

	public function group($entity) {
		return ClientGroups::registry($entity->group);
	}

	public function taxType($entity) {
		return TaxTypes::registry($entity->name);
	}

	public function amount($entity) {
		return new Price(
			(integer) $entity->amount,
			$entity->amount_currency,
			$entity->amount_type,
			(integer) $entity->amount_rate
		);
	}

	public function method($entity) {
		return AquisitionMethods::registry($entity->method);
	}

	// @deprecated
	public function hasAccess($entity, $user) {
		throw new Exception('Removed; map user to client group then find price by group.');
	}
}

// When given derive missing field values from tax type.
ProductPrices::applyFilter('save', function($self, $params, $chain) {
	$entity = $params['entity'];
	$data =& $params['data'];

	if (isset($data['tax_type'])) {
		$taxType = TaxTypes::registry($data['tax_type']);
		$data['amount_rate'] = $taxType->rate();
	}
	return $chain->next($self, $params, $chain);
});

?>