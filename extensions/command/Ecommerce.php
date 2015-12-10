<?php
/**
 * eCommerce Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
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
	public function autoInventory() {
		$this->out('Auto Inventory');

		foreach (Products::find('all') as $product) {
			$this->out("Product #{$product->number}, `{$product->title}`");
			$this->out('Current real stock: ' . $product->stock('real'));
			$this->out('Current virtual stock: ' . $product->stock('virtual'));
			$this->out('Current target stock: ' . $product->stock('target'));
			$this->out('Currently reserved of stock: ' . $product->stock('reserved'));

			$result = $product->save([
				'stock' => $product->stock('target')
			], [
				'whitelist' => ['id', 'stock']
			]);
			$this->out($result ? 'OK' : 'FAILED!');
		}
		$this->out('COMPLETED');
	}

	public function migrateTags() {
		$this->out('Migrate Auto/Size Tags');

		Tags::collect();

		foreach (ProductGroups::find('all') as $group) {
			$this->out('Tags before: ' . $group->tags);
			$result = $group->save([
				'title' => $group->title,
				'tags' => $group->tags
			], [
				'whitelist' => ['id', 'title', 'tags']
			]);
			$this->out('Tags after: ' . $group->tags);
			$this->out($result ? 'OK' : 'FAILED!');
		}

		foreach (ProductGroups::find('all') as $group) {
			$this->out('Tags before: ' . $group->tags);

			$group->removeTags(['look:available']);

			foreach ($group->tags(['serialized' => false]) as $tag) {
				if (preg_match('/^(size|part|look)\:[mslx\-\/]{1,3}$/i', $tag)) {
					$group->removeTags([$tag]);
				}
			}

			$result = $group->save([
				'tags' => $group->tags
			], [
				'whitelist' => ['id', 'tags']
			]);
			$this->out('Tags after: ' . $group->tags);
			$this->out($result ? 'OK' : 'FAILED!');
		}
		$this->out('COMPLETED');

		Tags::clean();
	}
}

?>