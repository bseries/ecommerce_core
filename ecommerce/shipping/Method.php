<?php
/**
 * eCommerce Core
 *
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace ecommerce_core\ecommerce\shipping;

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

			// Flag to indicate if other means handle delivery.
			'delegate' => true,

			// Set of conditions of which any must be fulfilled, so
			// that the method is made available to a user.
			'access' => ['user.role:admin'],

			// The fee applied when using the method. Can also be a callable.
			'price' => new NullPrice(),

			// Dependent on $format return either HTML or plaintext. Can be an anonymous function.
			'info' => null
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

	public function price($user, $cart) {
		return is_callable($value = $this->_config[__FUNCTION__]) ? $value($user, $cart) : $value;
	}

	public function info($entity, $context, $format, $renderer, $order) {
		return is_callable($value = $this->_config[__FUNCTION__]) ? $value($context, $format, $renderer, $order) : $value;
	}
}

?>