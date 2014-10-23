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

use lithium\storage\Cache;
use base_core\extensions\cms\Settings;
use ecommerce_core\models\Carts;
use ecommerce_core\models\ProductAttributes;
use ecommerce_core\models\ProductGroups;
use ecommerce_core\models\ProductPrices;
use ecommerce_core\models\ProductPriceGroups;
use Exception;
use lithium\util\Inflector;
use lithium\util\Collection;

class Products extends \base_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_products'
	];

	public $belongsTo = [
		'CoverMedia' => [
			'to' => 'base_media\models\Media',
			'key' => 'cover_media_id'
		]
	];

	protected static $_actsAs = [
		'base_media\extensions\data\behavior\Coupler' => [
			'bindings' => [
				'cover' => [
					'type' => 'direct',
					'to' => 'cover_media_id'
				],
				'media' => [
					'type' => 'joined',
					'to' => 'base_media\models\MediaAttachments'
				]
			]
		],
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\ReferenceNumber'
	];

	public static function init() {
		$model = static::_object();

		static::behavior('base_core\extensions\data\behavior\ReferenceNumber')->config(
			Settings::read('product.number')
		);
	}

	public function price($entity, $user) {
		foreach ($this->prices($entity) as $price) {
			if ($price->hasAccess($user)) {
				return $price->price($user);
			}
		}
		throw new Exception("Not legible for any price.");
	}

	// Returns prices for the product keyed by price group.
	//
	// When the sparse option is disabled will always return
	// a complete mapping of price groups => prices.
	public function prices($entity, array $options = []) {
		$options += [
			'sparse' => false
		];
		$results = [];

		if (!$options['sparse']) {
			foreach (ProductPriceGroups::find('all') as $group) {
				$results[$group->id] = ProductPrices::create([
					'group' => $group->id,
					'amount_currency' => $group->amountCurrency,
					'amount_type' => $group->amountType,
					'tax_type' => $group->taxType,
					'tax_rate' => $group->taxType()->rate
				]);
			}
		}
		$prices = ProductPrices::find('all', [
			'conditions' => [
				'ecommerce_product_id' => $entity->id
			]
		]);
		foreach ($prices as $price) {
			$results[$price->group] = $price;
		}
		return new Collection(['data' => $results]);
	}

	public function group($entity) {
		return ProductGroups::find('first', [
			'conditions' => [
				'id' => $entity->ecommerce_product_group_id
			]
		]);
	}

	public function attributes($entity) {
		return ProductAttributes::find('all', [
			'conditions' => [
				'ecommerce_product_id' => $entity->id
			]
		]);
	}

	protected static $_lastModifiedCarts = null;

	protected static $_lastModifiedShipments = null;

	public function stock($entity, $type = 'virtual') {
		$result = (integer) $entity->stock;

		if ($type !== 'virtual') {
			return $result;
		}
		$cacheKeyBase = 'stock_real_' . $entity->id;

		if (static::$_lastModifiedCarts === null) {
			static::$_lastModifiedCarts = Carts::find('first', [
				'order' => ['modified' => 'DESC'],
				'fields' => ['modified']
			]) ?: false;
		}
		$cacheKey = $cacheKeyBase . '_carts_' . (static::$_lastModifiedCarts ? md5(static::$_lastModifiedCarts->modified) : 'initial');

		$cartSubtract = [];
		if (!($cached = Cache::read('default', $cacheKey)) && $cached !== []) {
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
					$cartSubtract[$position->id] = (integer) $position->quantity;
				}
			}
			Cache::write('default', $cacheKey, $cartSubtract, Cache::PERSIST);
		} else {
			$cartSubtract = $cached;
		}

		if (static::$_lastModifiedShipments === null) {
			static::$_lastModifiedShipments = Shipments::find('first', [
				'order' => ['modified' => 'DESC'],
				'fields' => ['modified']
			]) ?: false;
		}
		$cacheKey = $cacheKeyBase . '_shipments_' . (static::$_lastModifiedShipments ? md5(static::$_lastModifiedShipments->modified) : 'initial');

		$shipmentSubtract = [];
		if (!($cached = Cache::read('default', $cacheKey)) && $cached !== []) {
			$shipments = Shipments::find('all', [
				'conditions' => [
					'status' => [
						// When in one of the following statuses, will decrement from real.
						'created',
						// When cancelled, we free stock and do not count it.
						'shipping-scheduled',
						'shipping-error'
						// After status was `shipping` the stock has been decremented already.
					]
				]
			]);
			foreach ($shipments as $shipment) {
				$positions = $shipment
					->order(['fields' => ['ecommerce_cart_id']])
					->cart(['fields' => ['id']])
					->positions();

				foreach ($positions as $position) {
					if ($position->ecommerce_product_id != $entity->id) {
						continue;
					}
					$shipmentSubtract[$position->id] = (integer) $position->quantity;
				}
			}
			Cache::write('default', $cacheKey, $shipmentSubtract, Cache::PERSIST);
		} else {
			$shipmentSubtract = $cached;
		}
		return $result - array_sum($cartSubtract) - array_sum($shipmentSubtract);
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

	// Save nested prices. We don't need to drop the whole prices
	// to get a clean start as price groups only get added never removed. This
	// however might change in future versions and behavior will then be
	// similar to the one how attributes are saved.
	if ($entity->prices) {
		foreach ($entity->prices as $key => $data) {
			if (!empty($data['id'])) {
				$item = ProductPrices::find('first', ['conditions' => ['id' => $data['id']]]);
				$item->set($data);
			} else {
				$item = ProductPrices::create($data + [
					'ecommerce_product_id' => $entity->id
				]);
			}
			if (!$item->save()) {
				return false;
			}
		}
	}

	// Save nested attributes. The to-be-saved attributes will replace
	// the current attributes as a whole. Thus on each save the whole
	// set of attributes needs to be provided.
	//
	// Key/Value Pairs must be unique.
	if ($entity->attributes) {
		$entity->attributes()->delete();

		$created = [];
		foreach ($entity->attributes as $key => $data) {
			if ($key === 'new') {
				continue;
			}
			if (in_array($data['key'], $created)) {
				return false;
			}
			$item = ProductAttributes::create($data + [
				'ecommerce_product_id' => $entity->id
			]);
			if (!$item->save()) {
				return false;
			}
			$created[] = $data['key'];
		}
	}

	return true;
});
Products::applyFilter('delete', function($self, $params, $chain) {
	$entity = $params['entity'];
	$result = $chain->next($self, $params, $chain);

	// Delete nested/dependent items.
	$entity->prices(['sparse' => true])->delete();
	$entity->attributes()->delete();

	return $result;
});

Products::init();

?>