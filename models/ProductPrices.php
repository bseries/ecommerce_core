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

namespace cms_ecommerce\models;

use cms_ecommerce\models\ProductPriceGroups;
use SebastianBergmann\Money\Money;
use SebastianBergmann\Money\Currency;

class ProductPrices extends \cms_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_product_prices'
	];

	public function group($entity) {
		return ProductPriceGroups::find('first', [
			'conditions' => [
				'id' => $entity->group
			]
		]);
	}

	public function price($entity, $type, $taxZone, $currency) {
		return new Money((integer) $entity->price_gross, new Currency($currency));
	}

	public function isLegibleFor($entity, $user) {
		$method = $this->group($entity)->data('legible');
		return $method($user);
	}
}

?>