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
use ecommerce_core\models\Products;
use ecommerce_core\models\Shipments;
use Exception;

class ShipmentPositions extends \base_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_shipment_positions'
	];

	protected $_actsAs = [
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

	public function amount($entity) {
		return new Price(
			(integer) $entity->amount,
			$entity->amount_currency,
			$entity->amount_type,
			(integer) $entity->amount_rate
		);
	}

	public function total($entity) {
		return $entity->amount()->multiply($entity->quantity);
	}

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