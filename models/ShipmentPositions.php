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
use ecommerce_core\models\Shipments;
use Exception;

class ShipmentPositions extends \base_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_shipment_positions'
	];

	protected static $_actsAs = [
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\Localizable' => [
			'fields' => [
				'amount' => 'money',
				'quantity' => 'decimal'
			]
		]
	];

	public function shipment($entity) {
		return Shipments::find('first', [
			'conditions' => [
				'id' => $entity->ecommerce_shipment_id
			]
		]);
	}

	public function amount($entity, $taxZone = null) {
		return new Price(
			$entity->amount,
			$entity->amount_currency,
			$entity->amount_type,
			$taxZone ?: $entity->shipment()->taxZone()
		);
	}

	public function totalAmount($entity, $taxZone = null) {
		return $entity->amount($taxZone)->multiply($entity->quantity);
	}

	// Assumes format "Foobar (#12345)".
	public function itemNumber($entity) {
		if (!preg_match('/\(#(.*)\)/', $entity->description, $matches)) {
			throw new Exception('Failed to extract item number from description.');
		}
		return $matches[1];
	}

	// Assumes format "Foobar (#12345)".
	public function itemTitle($entity) {
		if (!preg_match('/^(.*)\(/', $entity->description, $matches)) {
			throw new Exception('Failed to extract item title from description.');
		}
		return $matches[1];
	}
}

?>