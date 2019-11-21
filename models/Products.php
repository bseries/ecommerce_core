<?php
/**
 * eCommerce Core
 *
 * Copyright (c) 2014 David Persson - All rights reserved.
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
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
use lithium\aop\Filters;
use lithium\g11n\Message;
use lithium\storage\Cache;
use lithium\util\Collection;
use lithium\util\Inflector;
use lithium\util\Validator;

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
		extract(Message::aliases());
		$model = static::object();

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

		$model->validates['title'] = [
			[
				'notEmpty',
				'on' => ['create', 'update'],
				'message' => $t('This field cannot be empty.', ['scope' => 'ecommerce_core'])
			]
		];

		if (!static::behavior('ReferenceNumber')->config('generate')) {
			$model->validates['number'] = [
				'notEmpty' => [
					'notEmpty',
					'on' => ['create', 'update'],
					'last' => true,
					'message' => $t('This field cannot be empty.', ['scope' => 'ecommerce_core'])
				],
				'isUnique' => [
					'isUniqueReferenceNumber',
					'on' => ['create', 'update'],
					'message' => $t('This number is already in use.', ['scope' => 'ecommerce_core'])
				]
			];
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
		return $entity->prices ?: ProductPrices::find('all', [
			'conditions' => [
				'ecommerce_product_id' => $entity->id
			]
		]);
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

	// Ignores any reserved stock and looks only if enough local stock is available.
	public function canTakeStock($entity, $quantity = 1) {
		return $entity->stock('local') >= $quantity;
	}

	// "Takes" stock items and persistently decrements real stock.
	public function takeStock($entity, $quantity = 1) {
		$entity->decrement('stock', $quantity);

		if (Settings::read('stock.check') && $entity->stock < 0) {
			$message = "Capping stock at 0 for product `{$entity->id}`.";
			Logger::write('notice', $message);

			$entity->stock = 0;
		}

		$message  = "TAKE stock for product `{$entity->id}` by {$quantity}. ";
		$message .= "Real stock is now `{$entity->stock}`.";
		Logger::write('debug', $message);

		return $entity->save(null, ['whitelist' => ['stock'], 'validate' => false]);
	}

	// "Puts back" stock items and persistently increments real stock.
	public function putStock($entity, $quantity = 1) {
		$entity->increment('stock', $quantity);

		$message  = "PUT stock for product `{$entity->id}` by {$quantity}. ";
		$message .= "Real stock is now `{$entity->stock}`.";
		Logger::write('debug', $message);

		return $entity->save(null, ['whitelist' => ['stock'], 'validate' => false]);
	}

	public function canReserveStock($entity, $quantity = 1) {
		return $entity->stock('virtual') >= $quantity;
	}

	// Persistently reserves one or multiple items.
	public function reserveStock($entity, $quantity = 1) {
		$entity->increment('stock_reserved', $quantity);

		$message  = "RESERVE stock for product `{$entity->id}` by {$quantity}. ";
		$message .= "Reserved stock is now `{$entity->stock_reserved}`.";
		Logger::write('debug', $message);

		return $entity->save(null, ['whitelist' => ['stock_reserved'], 'validate' => false]);
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

// Implements a validation rule on nested collection (prices). We cannot use normal
// validation rules as they are not executed on fields not present in the schema.
Filters::apply(Products::class, 'validates', function($params, $next) {
	extract(Message::aliases());

	$entity = $params['entity'];
	$result = $next($params);;

	// Field was not submitted at all. Ensure we don't break save calls.
	if ($entity->prices === null) {
		return $result;
	}

	$hasPrice = false;
	foreach ((array) $entity->prices as $key => $value) {
		if ($key !== 'new') {
			$hasPrice = true;
			break;
		}
	}
	if (!$hasPrice) {
		$entity->errors([
			'prices' => $t('You must define at least one price.', ['scope' => 'ecommerce_core'])
		]);
		return false;
	}
	return $result;
});

Filters::apply(Products::class, 'save', function($params, $next) {
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

	if (!$result = $next($params)) {
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

Filters::apply(Products::class, 'delete', function($params, $next) {
	$entity = $params['entity'];
	$result = $next($params);

	// Delete nested/dependent items.
	$entity->prices()->delete();
	$entity->attributes()->delete();

	return $result;
});

Products::init();

?>
