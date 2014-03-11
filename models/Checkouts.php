<?php
/**
 * Bureau eCommerce
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace cms_ecommerce\models;

use Finite\StateMachine\StateMachine;
use Finite\State\StateInterface;
use cms_core\models\StatefulDocument;

class Checkouts extends \cms_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	public static function state($status) {
		$machine = new StateMachine();

		$machine->addState('unknown');
		$machine->addState('cart');
		$machine->addState('shipping');
		$machine->addState('payment');
		$machine->addState('confirmation');
		$machine->addState('success', StateInterface::TYPE_FINAL);

		$machine->addTransition('user-starts-checkout', 'unknown', 'cart');
		$machine->addTransition('user-confirms-products', 'cart', 'shipping');
		$machine->addTransition('user-confirms-shipping', 'shipping', 'payment');
		$machine->addTransition('user-confirms-payment', 'payment', 'confirmation');
		$machine->addTransition('user-confirms-checkout', 'confirmation', 'success');

		$machine->setObject(new StatefulDocument($status));
		$machine->initialize();

		return $machine;
	}
}

?>