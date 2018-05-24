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
	class="use-rich-index"
	data-endpoint="<?= $this->url([
		'action' => 'index',
		'page' => '__PAGE__',
		'orderField' => '__ORDER_FIELD__',
		'orderDirection' => '__ORDER_DIRECTION__',
		'filter' => '__FILTER__'
	]) ?>"
>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td data-sort="number" class="emphasize number table-sort"><?= $t('Order') ?>
					<td data-sort="status" class="status table-sort"><?= $t('Status') ?>
					<td data-sort="User.number" class="user table-sort"><?= $t('User') ?>
					<td data-sort="Invoice.number" class="number table-sort"><?= $t('Invoice') ?>
					<td data-sort="Invoice.status" class="status table-sort">…<?= $t('status') ?>
					<td data-sort="Shipment.number" class="number table-sort"><?= $t('Shipment') ?>
					<td data-sort="Shipment.status" class="status table-sort">…<?= $t('status') ?>
					<td data-sort="modified" class="date table-sort desc"><?= $t('Modified') ?>
					<?php if ($useSites): ?>
						<td data-sort="site" class="table-sort"><?= $t('Site') ?>
					<?php endif ?>
					<td class="actions">
						<?= $this->form->field('search', [
							'type' => 'search',
							'label' => false,
							'placeholder' => $t('Filter'),
							'class' => 'table-search',
							'value' => $this->_request->filter
						]) ?>
			</thead>
			<tbody>
				<?php foreach ($data as $item): ?>
					<?php $user = $item->user() ?>
				<tr data-id="<?= $item->id ?>">
					<td class="emphasize number"><?= $item->number ?: '–' ?>
					<td class="status"><?= $item->status ?: '–' ?>
					<td class="user">
						<?= $this->user->link($user) ?>
					<td class="number">
					<?php
					if ($sub = $item->invoice()) {
						echo $this->html->link($sub->number, [
							'library' => 'billing_invoice',
							'controller' => 'invoices', 'action' => 'edit',
							'id' => $sub->id
						]);
					} else {
						echo '–';
					}
					?>
					<td class="status">
						<?= $sub && $sub->status ? $sub->status : '–' ?>
					<td class="number">
					<?php
					if ($sub = $shipment = $item->shipment()) {
						echo $this->html->link($sub->number, ['controller' => 'shipments', 'library' => 'ecommerce_core', 'id' => $sub->id, 'action' => 'edit']);
					} else {
						echo '–';
					}
					?>
					<td class="status"><?= $sub && $sub->status ? $sub->status : '–' ?>
					<td class="date">
						<time datetime="<?= $this->date->format($item->modified, 'w3c') ?>">
							<?= $this->date->format($item->modified, 'date') ?>
						</time>
					<?php if ($useSites): ?>
						<td>
							<?= $item->site ?: '-' ?>
					<?php endif ?>
					<td class="actions">
						<?php if ($item->status !== 'processed'): ?>
							<?= $this->html->link($t('processed'), [
								'id' => $item->id,
								'action' => 'mark_processed',
								'library' => 'ecommerce_core'
							], ['class' => 'button']) ?>
						<?php endif ?>
						<?= $this->html->link($t('open'), [
							'id' => $item->id,
							'action' => 'edit',
							'library' => 'ecommerce_core'
						], ['class' => 'button']) ?>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>

	<?=$this->_render('element', 'paging', compact('paginator'), ['library' => 'base_core']) ?>

</article>
