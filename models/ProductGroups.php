<?php
/**
 * Boutique Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace ecommerce_core\models;

use ecommerce_core\models\Products;
use lithium\util\Inflector;
use li3_access\security\Access;

class ProductGroups extends \cms_core\models\Base {

	protected $_meta = [
		'source' => 'ecommerce_product_groups'
	];

	public $belongsTo = [
		'CoverMedia' => [
			'to' => 'cms_media\models\Media',
			'key' => 'cover_media_id'
		]
	];

	public $hasMany = [
		'Products' => [
			'to' => 'ecommerce_core\models\Products',
			'key' => 'ecommerce_product_group_id'
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
		'cms_core\extensions\data\behavior\Serializable' => [
			'fields' => [
				'access' => ','
			]
		]
	];

	public function products($entity, array $query = []) {
		return Products::find('all', [
			'conditions' => [
				'ecommerce_product_group_id' => $entity->id
			]
		] + $query);
	}

	public function slug($entity) {
		return strtolower(Inflector::slug($entity->title));
	}

	public function hasAccess($entity, $user) {
		return Access::check('entity', $user, ['request' => $entity], [
			'rules' => $entity->access
		]) === [];
	}
}

?>