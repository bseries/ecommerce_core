<?php
/**
 * eCommerce Core
 *
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
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

namespace ecommerce_core\ecommerce\aquisition;

use AD\Finance\Price\NullPrice;
use BadMethodCallException;
use li3_access\security\Access;

class Method {

	protected $_config = [];

	public function __construct(array $config) {
		if (isset($config['access'])) {
			$config['access'] = (array) $config['access'];
		}
		return $this->_config = $config + [
			'name' => null,

			// The (display) title of the method, can also be an anonymous function.
			'title' => null,
		];
	}

	public function __call($name, array $arguments) {
		if (!array_key_exists($name, $this->_config)) {
			throw new BadMethodCallException("Method or configuration `{$name}` does not exist.");
		}
		return $this->_config[$name];
	}

	public function hasAccess($user) {
		return Access::check(
			'entity',
			$user,
			$this,
			$this->_config['access']
		);
	}

	public function title() {
		return is_callable($value = $this->_config[__FUNCTION__]) ? $value() : $value;
	}
}

?>