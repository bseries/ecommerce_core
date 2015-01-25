<?php

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('orders')
	]
]);

?>
<article>

	<!--
	<div class="help">
		<?= $t('Cancelling an order will also cancel associated invoice and shipment - if possible.') ?>
		<?= $t('Once the order was shipped and the invoice paid, mark the order as `processed` to close it.') ?>
		<?= $t('When the order has been checked out, an email along with the invoice is sent to the user.') ?>
		<?= $t('The basic workflow is: 1. take the order and mark it as `processing`, 2. check the invoice and register payment, mark the invoice as `paid`, 3. handle the shipment, 4. mark the order as `processed`.')?>
	</div>

	<div class="actions">
		<?= $this->form->field('show_checked_out_only', [
			'type' => 'checkbox',
			'label'	=> $t('show checked out orders only'),
			'checked' => true,
			'value' => 'checked out',
			'data-filter' => 'status'
		]) ?>
	</div>
						<?= $this->form->field('search', [
							'type' => 'search',
							'label' => false,
							'placeholder' => $t('Filter'),
							'class' => 'list-search'
						]) ?>
	-->

	<?php if ($data->count()): ?>
		<table
			class="use-index-table"
			data-endpoint-sort="<?= $this->url([
				'action' => 'index',
				'page' => $paginator->getPages()->current,
				'orderField' => '__ORDER_FIELD__',
				'orderDirection' => '__ORDER_DIRECTION__'
			]) ?>"
		>
			<thead>
				<tr>
					<td data-sort="number" class="emphasize number table-sort"><?= $t('Number') ?>
					<td data-sort="status" class="status table-sort"><?= $t('Status') ?>
					<td data-sort="user" class="user list-sort"><?= $t('User') ?>
					<td data-sort="invoice-number" class="invoice-number table-sort"><?= $t('Invoice number') ?>
					<td data-sort="invoice-status" class="status invoice-status table-sort"><?= $t('Invoice status') ?>
					<td data-sort="shipment-number" class="shipment-number table-sort"><?= $t('Shipment number') ?>
					<td data-sort="shipment-status" class="status shipment-status table-sort"><?= $t('Shipment status') ?>
					<td class="date created"><?= $t('Created') ?>
					<td class="date modified list-sort desc"><?= $t('Modified') ?>
					<td class="actions">
			</thead>
			<tbody>
				<?php foreach ($data as $item): ?>
					<?php $user = $item->user() ?>
				<tr data-id="<?= $item->id ?>">
					<td class="emphasize number"><?= $item->number ?: '–' ?>
					<td class="status"><?= $item->status ? $statuses[$item->status] : '–' ?>
					<td class="user">
						<?php if ($user): ?>
							<?= $this->html->link($user->number, [
								'controller' => $user->isVirtual() ? 'VirtualUsers' : 'Users',
								'action' => 'edit', 'id' => $user->id,
								'library' => 'base_core'
							]) ?>
						<?php else: ?>
							-
						<?php endif ?>
					<td class="invoice-number">
					<?php
					if ($sub = $item->invoice()) {
						echo $this->html->link($sub->number, ['controller' => 'invoices', 'library' => 'billing_core', 'id' => $sub->id, 'action' => 'edit']);
					} else {
						echo '–';
					}
					?>
					<td class="status invoice-status"><?= $sub ? $invoiceStatuses[$sub->status] : '–' ?>
					<td class="shipment-number">
					<?php
					if ($sub = $shipment = $item->shipment()) {
						echo $this->html->link($sub->number, ['controller' => 'shipments', 'library' => 'ecommerce_core', 'id' => $sub->id, 'action' => 'edit']);
					} else {
						echo '–';
					}
					?>
					<td class="status shipment-status"><?= $sub ? $shipmentStatuses[$sub->status] : '–' ?>
					<td class="date created">
						<time datetime="<?= $this->date->format($item->created, 'w3c') ?>">
							<?= $this->date->format($item->created, 'date') ?>
						</time>
					<td class="date modified">
						<time datetime="<?= $this->date->format($item->modified, 'w3c') ?>">
							<?= $this->date->format($item->modified, 'date') ?>
						</time>
					<td class="actions">
<!--
						<?php if ($item->status == 'checked-out'): ?>
							<?= $this->html->link($t('processing'), ['id' => $item->id, 'action' => 'update_status', 'status' => 'processing' , 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
						<?php endif ?>
						<?php if ($item->status == 'checked-out'): ?>
							<?= $this->html->link($t('cancel'), ['id' => $item->id, 'action' => 'update_status', 'status' => 'cancelled' , 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
						<?php endif ?>
						<?php if ($item->status == 'processing'): ?>
							<?= $this->html->link($t('processed'), ['id' => $item->id, 'action' => 'update_status', 'status' => 'processed' , 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
						<?php endif ?>
-->
						<?= $this->html->link($t('open'), ['id' => $item->id, 'action' => 'edit', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>

	<?=$this->view()->render(['element' => 'paging'], compact('paginator'), ['library' => 'base_core']) ?>

</article>