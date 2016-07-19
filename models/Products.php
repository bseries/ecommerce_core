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

use Exception;
use base_core\extensions\cms\Settings;
use billing_core\billing\ClientGroups;
use ecommerce_core\ecommerce\aquisition\Methods as AquisitionMethods;
use ecommerce_core\models\Carts;
use ecommerce_core\models\ProductAttributes;
use ecommerce_core\models\ProductGroups;
use ecommerce_core\models\ProductPrices;
use lithium\analysis\Logger;
use lithium\storage\Cache;
use lithium\util\Collection;
use lithium\util\Inflector;

class Products extends \base_core\models\Base {

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

	protected $_actsAs = [
		'base_core\extensions\data\behavior\Sluggable',
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
				'title',
				'modified'
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
	public function price($entity, $user, $method) {
		$group = null;
		foreach (ClientGroups::registry(true) as $name => $item) {
			if ($item->conditions($user)) {
				$group = $name;
				break;
			}
		}
		if (!$group) {
			throw new Exception('Could not map user to client group.');
		}
		foreach ($this->prices($entity) as $price) {
			if ($price->group === $group && $price->method === $method) {
				return $price;
			}
		}
		return false;
	}

	// Returns prices for the product keyed by price group.
	public function prices($entity) {
		if (func_num_args() > 1) {
			$options = func_get_args()[1];
			if (isset($options['sparse'])) {
				trigger_error('Prices are now always sparse.', E_USER_DEPRECATED);
			}
		}
		$prices = $entity->prices ?: ProductPrices::find('all', [
			'conditions' => [
				'ecommerce_product_id' => $entity->id
			]
		]);
		$results = [];
		foreach ($prices as $price) {
			if (isset($results[$price->group . '#' . $price->method])) {
				trigger_error('Detected duplicate price group/method.', E_USER_NOTICE);
				continue;
			}
			$results[$price->group . '#' . $price->method] = $price;
		}
		return new Collection(['data' => $results]);
	}

	// There are 4 types of stock:
	//
	// - _local_ stock   : this is what the online system knows.
	//                     May become negative.
	// - _reserved_ stock: how many stock are reserved?
	//                     Can never become negative.
	// - _virtual_ stock : calculated from local stock subtracted by reserved stock.
	//                     Can never become negative.
	// - _target_ stock  : optional will only be used when another external system is involved.
	//                     May be negative.
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
				// Prevents virtual becoming negative when stock_reserved is positive
				// and real stock is 0. This should generally not happen as the system
				// is laid out to prevent this kind of case. However manual
				// intervention by the user (setting stock to 0) may break
				// these assumptions.

				// Note: Negative real stock is allowed to happen. But it is assumed
				// stock_reserved cannot be negative (that is safeguarded in unreserveStock).

				$result = $entity->stock - $entity->stock_reserved;

				if ($result < 0) {
					if (Settings::read('stock.check')) {
						$message  = "Prevented invalid virtual stock result";
						$message .= " for product `{$entity->id}` with reserved ";
						$message .= "{$entity->stock_reserved} > real {$entity->stock}; capping at 0.";
						Logger::write('notice', $message);

						return 0;
					}
				}
				return $result;

			case 'target':
				return $entity->stock_target;
			default:
				throw new Exception("Invalid stock type `{$type}`.");
		}
	}

	// "Takes" stock items and persistently decrements
	// real stock.
	public function takeStock($entity, $quantity = 1) {
		// if (Settings::read('stock.check') && $entity->stock('virtual') < $quantity) {
			// FIXME Fail here once we know all clients keep their numbers clean
			// and take only if there is stock.
		// }
		$entity->decrement('stock', $quantity);

		if (Settings::read('stock.check') && $entity->stock < 0) {
			$message = "Capping stock at 0 for product `{$entity->id}`.";
			Logger::write('notice', $message);

			$entity->stock = 0;
		}

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
		// if (Settings::read('stock.check') && $entity->stock('virtual') < $quantity) {
			// FIXME Fail here once we know all clients keep their numbers clean
			// and reserve only if there is stock.
		// }
		$entity->increment('stock_reserved', $quantity);

		$message  = "RESERVE stock for product `{$entity->id}` by {$quantity}. ";
		$message .= "Reserved stock is now `{$entity->stock_reserved}`.";
		Logger::write('debug', $message);

		return $entity->save(null, ['whitelist' => ['stock_reserved']]);
	}

	public function unreserveStock($entity, $quantity = 1) {
		$entity->decrement('stock_reserved', $quantity);

		if ($entity->stock_reserved < 0) {
			// FIXME Fail here once we know all clients keep their numbers clean
			// and unreserve only if it was previously reserved.
			$message = "Capping reserved stock at 0 for product `{$entity->id}`.";
			Logger::write('notice', $message);

			$entity->stock_reserved = 0;
		}
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

	// Save nested prices.
	$new = isset($data['prices']) ? $data['prices'] : [];
	foreach ($new as $key => $value) {
		if ($key === 'new') {
			continue;
		}
		// On nested forms id is always present, but on create empty.
		if (!empty($value['id'])) {
			$item = ProductPrices::find('first', [
				'conditions' => ['id' => $value['id']]
			]);
			if ($value['_delete']) {
				if (!$item->delete()) {
					return false;
				}
				continue;
			}
		} else {
			$item = ProductPrices::create($value + [
				'ecommerce_product_id' => $entity->id
			]);
		}
		if (!$item->save($value)) {
			return false;
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
	$entity->prices()->delete();
	$entity->attributes()->delete();

	return $result;
});

Products::init();

?>