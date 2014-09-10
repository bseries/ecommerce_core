<?php

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('carts')
	]
]);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?> use-list">
	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td data-sort="status" class="status list-sort"><?= $t('Status') ?>
					<td data-sort="order" class="order list-sort"><?= $t('Order') ?>
					<td data-sort="user" class="user list-sort"><?= $t('User') ?>
					<td data-sort="total-amount" class="total-amount list-sort"><?= $t('Total amount (net) ') ?>
					<td data-sort="total-quantity" class="total-quantity list-sort"><?= $t('Total quantity') ?>
					<td data-sort="created" class="date created list-sort desc"><?= $t('Created') ?>
					<td class="actions">
						<?= $this->form->field('search', [
							'type' => 'search',
							'label' => false,
							'placeholder' => $t('Filter'),
							'class' => 'list-search'
						]) ?>
			</thead>
			<tbody class="list">
				<?php foreach ($data as $item): ?>
					<?php $order = $item->order() ?>
					<?php $user = $order ? $order->user() : null?>
				<tr data-id="<?= $item->id ?>">
					<td class="status"><?= $statuses[$item->status] ?>
					<td class="order">
					<?php if ($order): ?>
						<?= $this->html->link($order->number, [
							'controller' => 'Orders', 'action' => 'edit', 'id' => $order->id,
							'library' => 'ecommerce_core'
						]) ?>
					<?php else: ?>
						-
					<?php endif ?>
					<td class="user">
					<?php if ($user): ?>
						<?= $this->html->link($user->number, [
							'controller' => $user->isVirtual() ? 'VirtualUsers' : 'Users',
							'action' => 'edit', 'id' => $user->id,
							'library' => 'base_core'
						]) ?>
					<?php else: ?>
						–
					<?php endif ?>
					<td class="total-amount">
					<?php if ($user): ?>
						<?= $this->money->format($item->totalAmount($user)->getNet(), 'money') ?: '–' ?>
					<?php else: ?>
						–
					<?php endif ?>
					<td class="total-quantity"><?= $item->totalQuantity() ?>
					<td class="date created">
						<time datetime="<?= $this->date->format($item->created, 'w3c') ?>">
							<?= $this->date->format($item->created, 'date') ?>
						</time>
					<td class="actions">
						<?php if (!$order && $item->status !== 'cancelled'): ?>
							<?= $this->html->link($t('cancel'), ['id' => $item->id, 'action' => 'cancel', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
						<?php endif ?>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>
</article>