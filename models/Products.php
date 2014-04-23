<?php
/**
 * Magasin Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace ecommerce_core\models;

use cms_core\extensions\cms\Settings;
use ecommerce_core\models\Carts;
use ecommerce_core\models\ProductGroups;
use ecommerce_core\models\ProductPrices;
use ecommerce_core\models\ProductPriceGroups;
use Exception;
use lithium\util\Inflector;

class Products extends \cms_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_products'
	];

	public $belongsTo = [
		'CoverMedia' => [
			'to' => 'cms_media\models\Media',
			'key' => 'cover_media_id'
		]
	];

	protected static $_actsAs = [
		'cms_media\extensions\data\behavior\Coupler' => [
			'bindings' => [
				'cover' => [
					'type' => 'direct',
					'to' => 'cover_media_id'
				],
				'media' => [
					'type' => 'joined',
					'to' => 'cms_media\models\MediaAttachments'
				]
			]
		],
		'cms_core\extensions\data\behavior\Timestamp',
		'cms_core\extensions\data\behavior\ReferenceNumber'
	];

	public static function init() {
		$model = static::_object();

		static::behavior('cms_core\extensions\data\behavior\ReferenceNumber')->config(
			Settings::read('product.number')
		);
	}

	public function price($entity, $user, $taxZone) {
		foreach ($this->prices($entity) as $price) {
			if ($price->isLegibleFor($user)) {
				return $price->price($taxZone);
			}
		}
		throw new Exception("Not legible for any price.");
	}

	public function prices($entity) {
		$results = [];

		foreach (ProductPriceGroups::find('all') as $group) {
			$results[$group->id] = ProductPrices::create([
				'group' => $group->id
			]);
		}
		$prices = ProductPrices::find('all', [
			'conditions' => [
				'ecommerce_product_id' => $entity->id
			]
		]);
		foreach ($prices as $price) {
			$results[$price->group] = $price;
		}
		return $results;
	}

	public function group($entity) {
		return ProductGroups::find('first', [
			'conditions' => [
				'id' => $entity->ecommerce_product_group_id
			]
		]);
	}

	public function stock($entity, $type = 'hard') {
		$result = (integer) $entity->stock;

		if ($type !== 'hard') {
			return $result;
		}
		$subtract = [];

		$carts = Carts::find('all', [
			'conditions' => [
				'status' => 'open'
			]
		]);
		foreach ($carts as $cart) {
			foreach ($cart->positions() as $position) {
				if ($position->ecommerce_product_id != $entity->id) {
					continue;
				}
				$subtract[$position->id] = (integer) $position->quantity;
			}
		}
		$shipments = Shipments::find('all', [
			'conditions' => [
				'status' => [
					'created',
					'shipping-scheduled',
					'shipping-error',
					'shipping'
				]
			]
		]);
		foreach ($shipments as $shipment) {
			foreach ($shipment->order()->cart()->positions() as $position) {
				if ($position->ecommerce_product_id != $entity->id) {
					continue;
				}
				$subtract[$position->id] = (integer) $position->quantity;
			}
		}
		return $result - array_sum($subtract);
	}

	public function slug($entity) {
		return strtolower(Inflector::slug($entity->title));
	}
}

Products::applyFilter('save', function($self, $params, $chain) {
	$entity = $params['entity'];
	$data =& $params['data'];

	// Create new product group.
	if (isset($data['ecommerce_product_group_id']) && $data['ecommerce_product_group_id'] == 'new') {
		$group = ProductGroups::create([
			'title' => $data['title'],
			'cover_media_id' => $data['cover_media_id']
		]);
		if (!$group->save()) {
			return false;
		}
		$data['ecommerce_product_group_id'] = $group->id;
	}

	if (!$result = $chain->next($self, $params, $chain)) {
		return false;
	}

	if (!$entity->prices) {
		return true;
	}
	// Save nested.
	$new = $entity->prices;

	foreach ($new as $key => $data) {
		if (!empty($data['id'])) {
			$item = ProductPrices::findById($data['id']);
			$item->set($data);
		} else {
			$item = ProductPrices::create($data);
			$item->ecommerce_product_id = $entity->id;
		}
		if (!$item->save()) {
			return false;
		}
	}
	return true;
});
Products::applyFilter('delete', function($self, $params, $chain) {
	$entity = $params['entity'];
	$result = $chain->next($self, $params, $chain);

	if ($result) {
		$data = ProductPrices::find('all', [
			'conditions' => ['ecommerce_product_id' => $entity->id]
		]);
		foreach ($data as $item) {
			$item->delete();
		}
	}
	return $result;
});

Products::init();

?>