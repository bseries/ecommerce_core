<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?> use-list">
	<h1 class="alpha"><?= $this->title($t('Orders')) ?></h1>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td data-sort="number" class="emphasize number list-sort desc"><?= $t('Number') ?>
					<td data-sort="status" class="status list-sort"><?= $t('Status') ?>
					<td data-sort="user" class="user list-sort"><?= $t('User') ?>
					<td data-sort="invoice-number" class="invoice-number list-sort"><?= $t('Invoice number') ?>
					<td data-sort="invoice-status" class="status invoice-status list-sort"><?= $t('Invoice status') ?>
					<td data-sort="shipment-id" class="shipment-id list-sort"><?= $t('Shipment ID') ?>
					<td data-sort="shipment-status" class="status shipment-status list-sort"><?= $t('Shipment status') ?>
					<td class="date created"><?= $t('Created') ?>
					<td>
						<?= $this->form->field('search', [
							'type' => 'search',
							'label' => false,
							'placeholder' => $t('Filter'),
							'class' => 'list-search'
						]) ?>
			</thead>
			<tbody class="list">
				<?php foreach ($data as $item): ?>
					<?php $user = $item->user() ?>
				<tr data-id="<?= $item->id ?>">
					<td class="emphasize number"><?= $item->number ?: '–' ?>
					<td class="status"><?= $item->status ?>
					<?php if ($user->isVirtual()): ?>
						<td class="user">
							<?= $this->html->link($user->name . '/' . $user->id, [
								'controller' => 'VirtualUsers', 'action' => 'edit', 'id' => $user->id, 'library' => 'cms_core'
							]) ?>
							(<?= $this->html->link('virtual', [
								'controller' => 'VirtualUsers', 'action' => 'index', 'library' => 'cms_core'
							]) ?>)
					<?php else: ?>
						<td class="user">
							<?= $this->html->link($user->name . '/' . $user->id, [
								'controller' => 'Users', 'action' => 'edit', 'id' => $user->id, 'library' => 'cms_core'
							]) ?>
							(<?= $this->html->link('real', [
								'controller' => 'Users', 'action' => 'index', 'library' => 'cms_core'
							]) ?>)
					<?php endif ?>
					<td class="invoice-number">
					<?php
					if ($sub = $item->invoice()) {
						echo $this->html->link($sub->number, ['controller' => 'invoices', 'library' => 'cms_billing', 'id' => $sub->id, 'action' => 'edit']);
					} else {
						echo '–';
					}
					?>
					<td class="status invoice-status"><?= $sub->status ?>
					<td class="shipment-id">
					<?php
					if ($sub = $shipment = $item->shipment()) {
						echo $this->html->link($sub->id, ['controller' => 'shipments', 'library' => 'ecommerce_core', 'id' => $sub->id, 'action' => 'edit']);
					} else {
						echo '–';
					}
					?>
					<td class="status shipment-status"><?= $sub->status ?>
					<td class="date created">
						<time datetime="<?= $this->date->format($item->created, 'w3c') ?>">
							<?= $this->date->format($item->created, 'date') ?>
						</time>
					<td>
						<nav class="actions">
							<?= $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
							<?= $this->html->link($t('paid'), ['id' => $item->id, 'action' => 'paid', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
							<?php if (!in_array($shipment->status, ['shipping', 'shipping-scheduled', 'shipped'])): ?>
								<?= $this->html->link($t('ship'), ['id' => $item->shipment()->id, 'action' => 'ship', 'controller' => 'Shipments', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
							<?php endif ?>
							<?= $this->html->link($t('edit'), ['id' => $item->id, 'action' => 'edit', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
						</nav>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>
</article>