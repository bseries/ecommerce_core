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

namespace ecommerce_core\extensions\command;

use ecommerce_core\models\Shipments;
use ecommerce_core\models\Products;
use ecommerce_core\models\Orders;

class Ecommerce extends \lithium\console\Command {

	/**
	 * Allows you run an inventory on the stock of your products.
	 * Will deduce orders with a shipment status of `shipping` from real stock.
	 */
	public function recoverStock() {
		$products = Products::find('all');
		$orders = Orders::find('all', [
			'conditions' => [
				'status' => ['checked-out', 'processed']
			]
		]);

		foreach ($products as $product) {
			$this->out("Product #{$product->number}, `{$product->title}`");
			$this->out('Current real stock: ' . $product->stock('real'));
			$this->out('Current virtual stock: ' . $product->stock('virtual'));
			$stock = $this->in('Enter new real stock:', ['default' => $product->stock('real')]);

			$this->out('Now checking how much we need to deduce from real...');
			foreach ($orders as $order) {
				$cartPositions = $order->cart()->positions();

				$shipment = $order->shipment();
				if (!in_array($shipment->status, ['shipping'])) {
					continue;
				}

				foreach ($cartPositions as $cartPosition) {
					$cartProduct = $cartPosition->product();

					if ($cartProduct->id == $product->id) {
						$this->out('Found order #' . $order->number . ' with shipment in status `shipping`.');
						$stock -= $cartPosition->quantity;
					}
				}
			}
			$this->out("Final real stock is {$stock}; saving...");
			$result = $product->save([
				'stock' => $stock
			], [
				'whitelist' => ['id', 'stock']
			]);
			$this->out($result ? 'OK' : 'FAILED!');
		}
	}
}

?>