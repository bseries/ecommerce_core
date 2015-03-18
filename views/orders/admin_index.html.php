<?php

use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'ecommerce_core', 'default' => $message]);
};

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('orders')
	]
]);

?>
<article
	class="use-index-table"
	data-endpoint-sort="<?= $this->url([
		'action' => 'index',
		'page' => $paginator->getPages()->current,
		'orderField' => '__ORDER_FIELD__',
		'orderDirection' => '__ORDER_DIRECTION__'
	]) ?>"
>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td data-sort="number" class="emphasize number table-sort"><?= $t('Order') ?>
					<td data-sort="status" class="status table-sort"><?= $t('Status') ?>
					<td data-sort="user.number" class="user table-sort"><?= $t('User') ?>
					<td data-sort="invoice.number" class="number table-sort"><?= $t('Invoice') ?>
					<td data-sort="invoice.status" class="status table-sort">…<?= $t('status') ?>
					<td data-sort="shipment.number" class="number table-sort"><?= $t('Shipment') ?>
					<td data-sort="shipment.status" class="status table-sort">…<?= $t('status') ?>
					<td data-sort="modified" class="date table-sort desc"><?= $t('Modified') ?>
					<td class="actions">
			</thead>
			<tbody>
				<?php foreach ($data as $item): ?>
					<?php $user = $item->user() ?>
				<tr data-id="<?= $item->id ?>">
					<td class="emphasize number"><?= $item->number ?: '–' ?>
					<td class="status"><?= $item->status ? $statuses[$item->status] : '–' ?>
					<td class="user number">
						<?php if ($user): ?>
							<?= $this->html->link($user->number, [
								'controller' => $user->isVirtual() ? 'VirtualUsers' : 'Users',
								'action' => 'edit', 'id' => $user->id,
								'library' => 'base_core'
							]) ?>
						<?php else: ?>
							-
						<?php endif ?>
					<td class="number">
					<?php
					if ($sub = $item->invoice()) {
						echo $this->html->link($sub->number, ['controller' => 'invoices', 'library' => 'billing_core', 'id' => $sub->id, 'action' => 'edit']);
					} else {
						echo '–';
					}
					?>
					<td class="status"><?= $sub ? $invoiceStatuses[$sub->status] : '–' ?>
					<td class="number">
					<?php
					if ($sub = $shipment = $item->shipment()) {
						echo $this->html->link($sub->number, ['controller' => 'shipments', 'library' => 'ecommerce_core', 'id' => $sub->id, 'action' => 'edit']);
					} else {
						echo '–';
					}
					?>
					<td class="status"><?= $sub ? $shipmentStatuses[$sub->status] : '–' ?>
					<td class="date">
						<time datetime="<?= $this->date->format($item->modified, 'w3c') ?>">
							<?= $this->date->format($item->modified, 'date') ?>
						</time>
					<td class="actions">
						<?= $this->html->link($t('open'), ['id' => $item->id, 'action' => 'edit', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>

	<?=$this->view()->render(['element' => 'paging'], compact('paginator'), ['library' => 'base_core']) ?>

</article>