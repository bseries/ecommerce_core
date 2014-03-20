<?php
/**
 * Bureau eCommerce
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace ecommerce_core\models;

use ecommerce_core\models\ProductPriceGroups;
use SebastianBergmann\Money\Money;
use SebastianBergmann\Money\Currency;

class ProductPrices extends \cms_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_product_prices'
	];

	protected static $_actsAs = [
		'cms_core\extensions\data\behavior\Localizable' => [
			'fields' => [
				'price_gross' => 'money'
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

	public function price($entity, $type, $taxZone, $currency) {
		$money = new Money((integer) $entity->price_gross, new Currency($currency));

		if ($type == 'gross') {
			return $money;
		}
		if (!$taxZone->rate) {
			return $money;
		}
		return $money->subtract($money->multiply($taxZone->rate / 100));
	}

	public function isLegibleFor($entity, $user) {
		$method = $this->group($entity)->data('legible');
		return $method($user);
	}
}

?>