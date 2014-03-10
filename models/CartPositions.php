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

class CartPositions extends \cms_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_cart_positions'
	];

	protected static $_actsAs = [
		'cms_core\extensions\data\behavior\Timestamp'
	];
}

?>