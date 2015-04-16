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
use billing_core\models\ClientGroups;
use Exception;
use lithium\util\Inflector;
use lithium\util\Collection;
use lithium\analysis\Logger;

class Products extends \base_core\models\Base {

	use \base_core\models\SlugTrait;

	protected $_meta = [
		'source' => 'ecommerce_products'
	];

	public $belongsTo = [
		'CoverMedia' => [
			'to' => 'base_media\models\Media',
			'key' => 'cover_media_id'
		],
		'Group' => [
			'to' => 'ecommerce_core\models\ProductGroups',
			'key' => 'ecommerce_product_group_id'
		]
	];

	public $hasMany = [
		'Prices' => [
			'to' => 'ecommerce_core\models\ProductPrices',
			'key' => 'ecommerce_product_id'
		],
		'Attributes' => [
			'to' => 'ecommerce_core\models\ProductAttributes',
			'key' => 'ecommerce_product_id'
		]
	];

	protected static $_actsAs = [
		'base_core\extensions\data\behavior\RelationsPlus',
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
		'base_core\extensions\data\behavior\ReferenceNumber',
		'base_core\extensions\data\behavior\Searchable' => [
			'fields' => [
				'number',
				'title'
			]
		]
	];

	public static function init() {
		static::behavior('base_core\extensions\data\behavior\ReferenceNumber')->config(
			Settings::read('product.number')
		);
		if (PROJECT_LOCALE !== PROJECT_LOCALES) {
			static::bindBehavior('li3_translate\extensions\data\behavior\Translatable', [
				'fields' => ['title', 'description'],
				'locale' => PROJECT_LOCALE,
				'locales' => explode(' ', PROJECT_LOCALES),
				'strategy' => 'inline'
			]);
		}
	}

	// Will autoselect the correct price for the user,
	// depending on its association in client group.
	public function price($entity, $user) {
		$group = ClientGroups::find('first', ['conditions' => compact('user')]);
		if (!$group) {
			throw new Exception('Could not map user to client group.');
		}

		foreach ($this->prices($entity) as $price) {
			if ($price->group === $group->id) {
				return $price;
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
			foreach (ClientGroups::find('all') as $group) {
				$results[$group->id] = ProductPrices::create([
					'group' => $group->id,
					'amount_currency' => $group->amountCurrency,
					'amount_type' => $group->amountType,
					'amount_rate' => $group->taxType()->rate
				]);
			}
		}
		$prices = $entity->prices ?: ProductPrices::find('all', [
			'conditions' => [
				'ecommerce_product_id' => $entity->id
			]
		]);
		foreach ($prices as $price) {
			$results[$price->group] = $price;
		}
		return new Collection(['data' => $results]);
	}

	// There are 4 types of stock:
	//
	// - _local_ stock, this is what the online system knows.
	// - _reserved_ stock, how many stock are reserved?
	// - _virtual_ stock, calculated from local stock subtracted by reserved stock.
	// - _target_ stock, optional will only be used when another external system is involved.
	//
	// The stock counts are denormalized in the mode schema for
	// performance reasons. Stock is a pretty common field to
	// display or sort for and can only be cached in a complex way.
	//
	public function stock($entity, $type = 'virtual') {
		switch ($type) {
			case 'real':
			case 'local':
				return $entity->stock;
			case 'reserved':
				return $entity->stock_reserved;
			case 'virtual':
				return $entity->stock - $entity->stock_reserved;
			case 'target':
				return $entity->stock_target;
			default:
				throw new Exception("Invalid stock type `{$type}`.");
		}
	}

	// "Takes" stock items and persistently decrements
	// real stock.
	public function takeStock($entity, $quantity = 1) {
		$entity->decrement('stock', $quantity);

		$message  = "TAKE stock for product `{$entity->id}` by {$quantity}. ";
		$message .= "Real stock is now `{$entity->stock}`.";
		Logger::write('debug', $message);

		return $entity->save(null, ['whitelist' => ['stock']]);
	}

	// "Puts back" stock items and persistently increments
	// real stock.
	public function putStock($entity, $quantity = 1) {
		$entity->increment('stock', $quantity);

		$message  = "PUT stock for product `{$entity->id}` by {$quantity}. ";
		$message .= "Real stock is now `{$entity->stock}`.";
		Logger::write('debug', $message);

		return $entity->save(null, ['whitelist' => ['stock']]);
	}

	// Persistently reserves one or multiple items.
	public function reserveStock($entity, $quantity = 1) {
		$entity->increment('stock_reserved', $quantity);

		$message  = "RESERVE stock for product `{$entity->id}` by {$quantity}. ";
		$message .= "Reserved stock is now `{$entity->stock_reserved}`.";
		Logger::write('debug', $message);

		return $entity->save(null, ['whitelist' => ['stock_reserved']]);
	}

	public function unreserveStock($entity, $quantity = 1) {
		$entity->decrement('stock_reserved', $quantity);

		$message  = "UNRESERVE stock for product `{$entity->id}` by {$quantity}. ";
		$message .= "Reserved stock is now `{$entity->stock_reserved}`.";
		Logger::write('debug', $message);

		return $entity->save(null, ['whitelist' => ['stock_reserved']]);
	}
}

Products::applyFilter('save', function($self, $params, $chain) {
	$entity = $params['entity'];
	$data =& $params['data'];

	// Create new product group.
	if (isset($data['ecommerce_product_group_id']) && $data['ecommerce_product_group_id'] == 'new') {
		$group = [
			'cover_media_id' => $data['cover_media_id']
		];
		if (isset($data['i18n'])) {
			$group['i18n']['title'] = $data['i18n']['title'];
		} else {
			$group['title']  = $data['title'];
		}
		$group = ProductGroups::create($group);

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
		$entity->attributes(['force' => true])->delete();

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