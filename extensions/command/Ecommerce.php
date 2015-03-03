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
use billing_core\models\InvoicePositions;
use ecommerce_core\models\Shipments;
use ecommerce_core\models\ShipmentPositions;
use ecommerce_core\models\Products;
use ecommerce_core\models\ProductGroups;
use ecommerce_core\models\Orders;
use ecommerce_core\models\ProductPrices;
use ecommerce_core\models\Carts;

class Ecommerce extends \lithium\console\Command {

	/**
	 * Allows you run an inventory on the stock of your products.
	 * Will deduce orders with a shipment status of `shipping` from real stock.
	 */
	public function takeManualInventory() {
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
			if ($result->group == 'merchant') {
				$result->group = 'DE.merchant';
			} else {
				$result->group = 'DE.customer';
			}
			$r = $result->save(null, [
				'validate' => false,
				'whitelist' => ['group']
			]);
			$this->out("ID {$result->id}: " . ($r ? 'OK' : 'FAILED!'));
		}
		$this->out('All done.');

		$this->out('Migrating invoices...');
		$results = Invoices::find('all');

		foreach ($results as $result) {
			if (!$result->user_id) {
				$result->user_id = null;
			}
			if (!$result->virtual_user_id) {
				$result->virtual_user_id = null;
			}

			$r = $result->save(null, [
				'validate' => false,
				'whitelist' => ['terms', 'user_id', 'virtual_user_id']
			]);
			$this->out("ID {$result->id}: " . ($r ? 'OK' : 'FAILED!'));
		}

		$this->out('Migrating invoice positions...');
		$results = InvoicePositions::find('all');

		foreach ($results as $result) {
			if (!$result->user_id) {
				$result->user_id = null;
			}
			if (!$result->virtual_user_id) {
				$result->virtual_user_id = null;
			}
			if ($invoice = $result->invoice()) {
				$result->user_id = $invoice->user_id;
				$result->virtual_user_id = $invoice->virtual_user_id;
			}
			$r = $result->save(null, [
				'validate' => false,
				'whitelist' => ['user_id', 'virtual_user_id']
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
					'amount' => $iPos->amount,
					'tax_type' => $iPos->tax_type,
					'tax_rate' => $iPos->tax_rate
				]);

				$r = $sPos->save(null, [
					'validate' => false
				]);
				$this->out("ID {$sPos->id}: " . ($r ? 'OK' : 'FAILED!'));
			}
		}

		$this->_ensureVatOnInvoices();
		$this->out('All done 1.0 -> 1.3.');
	}


	public function migrateTo1213() {
		$this->_ensureVatOnInvoices();
		$this->_convertStock();

		$this->out('All done 1.2 -> 1.3.');
	}

	protected function _convertStock() {
		$this->out('Converting stock...');
		$results = Products::find('all');

		foreach ($results as $product) {
			$virtual = $this->_oldStock($product, 'virtual');
			$reserved = $product->stock - $virtual;

			$r = $product->save([
				'stock' => $product->stock,
				'stock_reserved' => $reserved
			], ['whitelist' => ['stock', 'stock_reserved']]);

			$this->out("ID {$result->id}: " . ($r ? 'OK' : 'FAILED!'));
		}
	}

	protected function _oldStock($entity, $stock = 'virtual') {
		$result = (integer) $entity->stock;

		if ($type !== 'virtual') {
			return $result;
		}

		$cartSubtract = [];
		$carts = Carts::find('all', [
			'conditions' => [
				'status' => 'open'
			]
		]);
		foreach ($carts as $cart) {
			foreach ($cart->positions() as $position) {
				if ($position->ecommerce_product_id != $entity->id) {
					continue;
				}
				$cartSubtract[$position->id] = (integer) $position->quantity;
			}
		}

		$shipmentSubtract = [];
		$shipments = Shipments::find('all', [
			'conditions' => [
				'status' => [
					// When in one of the following statuses, will decrement from real.
					'created',
					// When cancelled, we free stock and do not count it.
					'shipping-scheduled',
					'shipping-error',
					'shipping',
					// When status is `shipped` the stock has been decremented already.
				]
			]
		]);
		foreach ($shipments as $shipment) {
			$positions = $shipment
				->order(['fields' => ['ecommerce_cart_id']])
				->cart(['fields' => ['id']])
				->positions();

			foreach ($positions as $position) {
				if ($position->ecommerce_product_id != $entity->id) {
					continue;
				}
				$shipmentSubtract[$position->id] = (integer) $position->quantity;
			}
		}
		return $result - array_sum($cartSubtract) - array_sum($shipmentSubtract);
	}

	protected function _ensureVatOnInvoices() {
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
	}


}

?>