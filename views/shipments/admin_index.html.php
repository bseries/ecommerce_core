<?php

use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'ecommerce_core', 'default' => $message]);
};

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('shipments')
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

	<div class="top-actions">
		<?= $this->html->link($t('shipment'), ['action' => 'add'], ['class' => 'button add']) ?>
	</div>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td data-sort="number" class="number emphasize table-sort"><?= $t('Number') ?>
					<td data-sort="status" class="status table-sort"><?= $t('Status') ?>
					<td data-sort="User.number" class="user table-sort"><?= $t('Recipient') ?>
					<td data-sort="order" class="order number table-sort"><?= $t('Order') ?>
					<td data-sort="method" class="method table-sort"><?= $t('Method') ?>
					<td data-sort="modified" class="date modified table-sort desc"><?= $t('Modified') ?>
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
					<td class="number"><?= $item->number ?>
					<td class="status"><?= $item->status ?>
					<td class="user">
						<?= $this->user->link($user) ?>
					<td class="order number">
					<?php
						if ($order = $item->order()) {
							echo $this->html->link($order->number, [
								'controller' => 'Orders', 'action' => 'edit', 'id' => $order->id,
								'library' => 'ecommerce_core'
							]);
						} else {
							echo '–';
						}
					?>
					<td class="method"><?= $item->method ?>
					<td class="date modified">
						<time datetime="<?= $this->date->format($item->modified, 'w3c') ?>">
							<?= $this->date->format($item->modified, 'date') ?>
						</time>
					<?php if ($useSites): ?>
						<td>
							<?= $item->site ?: '-' ?>
					<?php endif ?>
					<td class="actions">
						<?= $this->html->link($t('PDF'), [
							'id' => $item->id, 'action' => 'export_pdf'
						], ['class' => 'button', 'download' => "shipment_{$item->number}.pdf"]) ?>
						<?= $this->html->link($t('open'), [
							'id' => $item->id, 'action' => 'edit'
						], ['class' => 'button']) ?>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>

	<?=$this->_render('element', 'paging', compact('paginator'), ['library' => 'base_core']) ?>

</article>
