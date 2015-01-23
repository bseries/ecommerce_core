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

namespace ecommerce_core\extensions\command;

use billing_core\models\Invoices;
use ecommerce_core\models\Shipments;
use ecommerce_core\models\ShipmentPositionss;
use ecommerce_core\models\Products;
use ecommerce_core\models\ProductGroups;
use ecommerce_core\models\Orders;
use ecommerce_core\models\ProductPrices;

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

	public function migrate10to13() {
		// Assumes source was all German and certain
		// tax customer type mappings are valid.

		$this->out('Migrating product prices...');
		$results = ProductPrices::find('all');

		foreach ($results as $result) {
			$result->tax_type = 'DE.vat.standard';
			$result->tax_rate = 19;

			if ($result->group == 'merchant') {
				$result->group = 'DE.merchant';
			} else {
				$result->group = 'DE.customer';
			}
			$r = $result->save(null, [
				'validate' => false,
				'whitelist' => ['tax_type', 'tax_rate', 'group']
			]);
			$this->out("ID {$result->id}: " . ($r ? 'OK' : 'FAILED!'));
		}
		$this->out('All done.');


		$this->out('Migrating shipments...');
		$results = Shipments::find('all');

		foreach ($results as $result) {
			$result->tax_rate = 19;
			$result->tax_note = 'Enthält 19% MwSt.';

			$r = $result->save(null, [
				'validate' => false,
				'whitelist' => ['tax_note', 'tax_rate']
			]);
			$this->out("ID {$result->id}: " . ($r ? 'OK' : 'FAILED!'));
		}
		$this->out('All done.');

		$this->out('Migrating shipment positions...');
		$orders = Orders::find('all', [
			'conditions' => [
				'status' => 'checked-out'
			]
		]);
		// $invoices = Invoices::find('all');
		// $shipments = Shipments::find('all');

		foreach ($orders as $order) {
			$shipment = $order->shipment();
			$invoice = $order->invoice();
			$user = $order->user();

			foreach ($invoice->positions() as $iPos) {
				// Skip non items.
				try {
					$iPos->itemNumber();
				} catch (\Exception $e) {
					continue;
				}

				$sPos = ShipmentPositions::create([
					'ecommerce_shipment_id' => $shipment->id,
					'description' => $iPos->description,
					'quantity' => $iPos->quantity,
					'amount_currency' => $iPos->amount_currency,
					'amount_type' => $iPos->amount_type,
					'amount' => $iPos->amount
				]);

				$r = $sPos->save(null, [
					'validate' => false
				]);
				$this->out("ID {$sPos->id}: " . ($r ? 'OK' : 'FAILED!'));
			}
		}
		$this->out('All done.');

		$this->migrateTo13();
	}

	public function migrateTo13() {
		$this->out('Ensuring user_vat_reg_no is set on all invoices...');
		$results = Invoices::find('all');

		foreach ($results as $result) {
			if (!$user = $result->user()) {
				$this->out("ID {$result->id}: No user found!");
				continue;
			}
			$result->user_vat_reg_no = $user->vat_reg_no;

			$r = $result->save(null, [
				'validate' => false,
				'whitelist' => ['user_vat_reg_no']
			]);
			$this->out("ID {$result->id}: " . ($r ? 'OK' : 'FAILED!'));
		}
		$this->out('All done.');
	}
}

?>