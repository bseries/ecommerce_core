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

use cms_billing\models\Invoices;
use cms_core\extensions\cms\Settings;
use DateTime;

class Orders extends \cms_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_orders'
	];

	public $belongsTo = [
		'Invoice' => [
			'class' => 'cms_billing\models\Invoice',
			'key' => 'billing_invoice_id'
		],
		'Shipment' => [
			'class' => 'cms_ecommerce\models\Shipment',
			'key' => 'ecommerce_shipment_id'
		]
	];

	protected static $_actsAs = [
		'cms_core\extensions\data\behavior\Timestamp'
	];

	public static function nextNumber() {
		$pattern = Settings::read('orderNumberPattern');

		$item = static::find('first', [
			'conditions' => [
				'number' => 'LIKE ' . strftime($pattern['prefix']) . '%'
			],
			'order' => ['number' => 'DESC'],
			'fields' => ['number']
		]);
		if ($item && ($number = $item->number)) {
			$number++;
		} else {
			$number = strftime($pattern['prefix']) . sprintf($pattern['number'], 1);
		}
		return $number;
	}

	public function shipment($entity) {
		return false;
	}

	public function invoice($entity) {
		return Invoices::find('first', [
			'conditions' => [
				'id' => $entity->billing_invoice_id
			]
		]);
	}
}

Orders::applyFilter('create', function($self, $params, $chain) {
	static $useFilter = true;

	$entity = $chain->next($self, $params, $chain);

	if (!$useFilter) {
		return $entity;
	}

	if (!$entity->exists()) {
		$useFilter = false;
		$entity->number = Orders::nextNumber();
		$useFilter = true;
	}
	return $entity;
});

?>