<?php
/**
 * eCommerce Core
 *
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace ecommerce_core\ecommerce\product;

use BadMethodCallException;

class Attribute {

	protected $_config = [];

	public function __construct(array $config) {
		return $this->_config = $config + [
			'name' => null,

			// The (display) title of the method, can also be an anonymous function.
			'title' => null
		];
	}

	public function __call($name, array $arguments) {
		if (!array_key_exists($name, $this->_config)) {
			throw new BadMethodCallException("Method or configuration `{$name}` does not exist.");
		}
		return $this->_config[$name];
	}

	public function title() {
		return is_callable($value = $this->_config[__FUNCTION__]) ? $value() : $value;
	}
}

?>
