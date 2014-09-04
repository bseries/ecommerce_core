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

use base_media\models\Media;

Media::registerDependent('ecommerce_core\models\Products', [
	'cover' => 'direct',
	'media' => 'joined'
]);
Media::registerDependent('ecommerce_core\models\ProductGroups', [
	'cover' => 'direct',
	'media' => 'joined'
]);

?>