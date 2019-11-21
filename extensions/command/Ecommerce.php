<?php
/**
 * eCommerce Core
 *
 * Copyright (c) 2014 David Persson - All rights reserved.
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace ecommerce_core\extensions\command;

use ecommerce_core\models\Products;
use ecommerce_core\models\ProductGroups;
use base_tag\models\Tags;

class Ecommerce extends \lithium\console\Command {

	/**
	 * Allows you run an inventory on the stock of your products.
	 */
	public function manualInventory() {
		$this->out('Manual Inventory');

		foreach (Products::find('all') as $product) {
			$this->out("Product #{$product->number}, `{$product->title}`");
			$this->out('Current real stock: ' . $product->stock('real'));
			$this->out('Current virtual stock: ' . $product->stock('virtual'));
			$this->out('Current target stock: ' . $product->stock('target'));
			$this->out('Currently reserved of stock: ' . $product->stock('reserved'));

			$stock = $this->in('Enter new real stock:', ['default' => $product->stock('real')]);

			$result = $product->save([
				'stock' => $stock
			], [
				'whitelist' => ['id', 'stock']
			]);
			$this->out($result ? 'OK' : 'FAILED!');
		}
		$this->out('COMPLETED');
	}

	/**
	 * Will use stock_target as new stock. Run only when stock_target
	 * is actually used. Otherwise will reset all stocks to 0.
	 */
	public function repairStockFromTargetStock() {
		$this->header('Repair Stock using Target stock');

		foreach (Products::find('all') as $product) {
			$this->out("--- Product #{$product->number}, `{$product->title}` ---");
			$this->out('real    : ' . $product->stock('real'));
			$this->out('target  : ' . $product->stock('target'));

			$result = $product->save([
				'stock' => $product->stock('target')
			], [
				'whitelist' => ['id', 'stock'],
				'modified' => false
			]);
			$this->out($result ? 'OK' : 'FAILED!');
		}
		$this->out('COMPLETED');
	}

	public function repairTargetStockFromStock() {
		$this->header('Repair Target Stock using Stock');

		foreach (Products::find('all') as $product) {
			if ($product->stock('real') === $product->stock('target')) {
				continue;
			}
			$this->out("--- Product #{$product->number}, `{$product->title}` ---");
			$this->out('real    : ' . $product->stock('real'));
			$this->out('target  : ' . $product->stock('target'));

			$result = $product->save([
				'stock_target' => $product->stock('real')
			], [
				'whitelist' => ['id', 'stock_target'],
				'modified' => false
			]);
			$this->out($result ? 'OK' : 'FAILED!');
		}
		$this->out('COMPLETED');
	}

	public function repairInvalidStock() {
		$this->header('Repair Stock field below 0 and Reserved but no Real');

		foreach (Products::find('all') as $product) {
			$data = [];
			foreach (['stock', 'stock_reserved', 'stock_target'] as $field) {
				if ($product->{$field} < 0) {
					$data[$field] = 0;
				}
			}
			if (!$data) {
				continue;
			}

			$this->out("--- Product #{$product->number}, `{$product->title}` ---");
			$this->out('real    : ' . $product->stock('real'));
			$this->out('target  : ' . $product->stock('target'));
			$this->out('virtual : ' . $product->stock('virtual'));
			$this->out('reserved: ' . $product->stock('reserved'));

			$result = $product->save($data, [
				'whitelist' => $a = array_merge(['id'], array_keys($data)),
				'modified' => false
			]);
			$this->out($result ? 'OK' : 'FAILED!');
		}
		$this->header('COMPLETED');
	}

	// @deprecated
	public function migrateTags() {
		$this->out('Migrate Auto/Size Tags');

		$this->out('Collecting tags...');
		Tags::collect();

		$this->out('Updating auot tags...');
		foreach (ProductGroups::find('all') as $group) {
			$before = $group->tags;

			$group->removeTags(['look:available']);

			foreach ($group->tags(['serialized' => false]) as $tag) {
				if (preg_match('/^(size|part|look)\:[mslx\-\/]{1,3}$/i', $tag)) {
					$group->removeTags([$tag]);
				}
			}

			$result = $group->save([
				'title' => $group->title,
				'tags' => $group->tags
			], [
				'whitelist' => ['id', 'title', 'tags']
			]);
			if ($group->tags != $before) {
				$this->out('Tags before: ' . $before);
				$this->out('Tags after: ' . $group->tags);
				$this->out($result ? 'OK' : 'FAILED!');
			}
		}
		$this->out('COMPLETED');

		Tags::clean();
	}
}

?>