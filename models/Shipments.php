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

use cms_core\models\Addresses;

class Shipments extends \cms_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_shipments'
	];

	protected static $_actsAs = [
		'cms_core\extensions\data\behavior\Timestamp'
	];

	public static $enum = [
		'status' => [
			'created',
			'scheduled-for-transmission',
			'transmitted-in-progress',
			'in-progress',
			'failed',
			'done'
		]
	];

	public function user($entity) {
		return $entity->order()->user();
	}

	public function order($entity) {
		return Orders::find('first', [
			'conditions' => [
				'ecommerce_shipment_id' => $entity->id
			]
		]);
	}

	public function address($entity) {
		$data = $entity->data();
		$data += $entity->order()->data(); // Add user fields.

		return Addresses::createFromPrefixed('address_', $data);
	}
}

?>