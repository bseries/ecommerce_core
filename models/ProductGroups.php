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
 * License. If not, see https://atelierdisko.de/licenses.
 */

namespace ecommerce_core\models;

use base_core\extensions\cms\Settings;
use lithium\g11n\Message;

class ProductGroups extends \base_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_product_groups'
	];

	public $belongsTo = [
		'CoverMedia' => [
			'to' => 'base_media\models\Media',
			'key' => 'cover_media_id'
		]
	];

	public $hasMany = [
		'Products' => [
			'to' => 'ecommerce_core\models\Products',
			'key' => 'ecommerce_product_group_id'
		]
	];

	protected $_actsAs = [
		'base_core\extensions\data\behavior\Access',
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
		'base_core\extensions\data\behavior\Serializable' => [
			'fields' => [
				'access' => ','
			]
		],
		'li3_taggable\extensions\data\behavior\Taggable' => [
			'field' => 'tags',
			'tagsModel' => 'base_tag\models\Tags',
			'filters' => ['strtolower']
		],
		'base_core\extensions\data\behavior\Searchable' => [
			'fields' => [
				'title',
				'tags',
				'modified'
			]
		]
	];

	public static function init() {
		extract(Message::aliases());

		if (PROJECT_LOCALE !== PROJECT_LOCALES) {
			static::bindBehavior('li3_translate\extensions\data\behavior\Translatable', [
				'fields' => ['title', 'description'],
				'locale' => PROJECT_LOCALE,
				'locales' => explode(' ', PROJECT_LOCALES),
				'strategy' => 'inline'
			]);
		}
		if (Settings::read('productGroup.useAutoTagging')) {
			static::behavior('Taggable')->config('autoMatch', ['title']);
		}
		$model->validates['tags'] = [
			[
				'noSpacesInTags',
				'on' => ['create', 'update'],
				'message' => $t('Tags cannot contain spaces.', ['scope' => 'ecommerce_core'])
			]
		];
	}

	public function hasAnyPublishedProducts($entity) {
		return (boolean) $entity->products()->find(function($p) {
			return $p->is_published;
		})->count();
	}
}

ProductGroups::init();

?>