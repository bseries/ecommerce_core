<?php
/**
 * Magasin Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace ecommerce_core\extensions\command;

use ecommerce_core\models\Orders;
use ecommerce_core\models\Carts;

class Ecommerce extends \lithium\console\Command {

	public function expire() {
		$data = Orders::find('all', [
			'conditions' => [
				'status' => 'checking-out'
			]
		]);
		foreach ($data as $item) {
			if ($item->isExpired()) {
				$item->save(['status' => 'expired']);
				$item->cart()->save(['status' => 'expired']);
				$this->out("Order `{$item->id}` and associated cart expired.");
			}
		}

		// Not all carts must have an associated order, i.e.
		// when checkout never begun.
		$data = Carts::find('all', [
			'conditions' => [
				'status' => 'open'
			]
		]);
		foreach ($data as $item) {
			if ($item->isExpired()) {
				$item->save(['status' => 'expired']);
				$this->out("Cart `{$item->id}` expired.");
			}
		}
	}
}

?>