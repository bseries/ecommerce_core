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
use ecommerce_core\models\Products;
use ecommerce_core\models\Shipments;

class ShipmentPositions extends \base_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_shipment_positions'
	];

	protected static $_actsAs = [
		'base_core\extensions\data\behavior\RelationsPlus',
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\Localizable' => [
			'fields' => [
				'amount' => 'money',
				'quantity' => 'decimal'
			]
		]
	];

	public $belongsTo = [
		'Shipment' => [
			'to' => 'ecommerce_core\models\Shipments',
			'key' => 'ecommerce_shipment_id'
		]
	];

	// Assumes format "Foobar (#12345)".
	public function product($entity) {
		if (!preg_match('/\(#(.*)\)/', $entity->description, $matches)) {
			return false;
		}
		return Products::find('first', [
			'conditions' => [
				'number' => $matches[1]
			]
		]);
	}

	/* Deprecated */

	public function totalAmount($entity) {
		trigger_error('ShipmentPositions::totalAmount has been deprecated in favor of total().', E_USER_DEPRECATED);
		return $entity->total();
	}

	// Assumes format "Foobar (#12345)".
	public function itemNumber($entity) {
		trigger_error('itemNumber() has been deprecated, use product() instead.', E_USER_DEPRECATED);

		if (!preg_match('/\(#(.*)\)/', $entity->description, $matches)) {
			throw new Exception('Failed to extract item number from description.');
		}
		return $matches[1];
	}

	// Assumes format "Foobar (#12345)".
	public function itemTitle($entity) {
		trigger_error('itemTitle() has been deprecated, use product() instead.', E_USER_DEPRECATED);

		if (!preg_match('/^(.*)\(/', $entity->description, $matches)) {
			throw new Exception('Failed to extract item title from description.');
		}
		return $matches[1];
	}

}

?>