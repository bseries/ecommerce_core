<?php

use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'ecommerce_core', 'default' => $message]);
};

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('carts')
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
					<td data-sort="status" class="status table-sort"><?= $t('Status') ?>
					<td data-sort="order.number" class="order table-sort"><?= $t('Order') ?>
					<td data-sort="user.number" class="user table-sort"><?= $t('User') ?>
					<td><?= $t('Total value (net) ') ?>
					<td data-sort="total-quantity" class="total-quantity table-sort"><?= $t('Total quantity') ?>
					<td data-sort="modified" class="date modified table-sort desc"><?= $t('Modified') ?>
					<td class="actions">
			</thead>
			<tbody>
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
						<?= $this->money->format($R = $item->totalValues('net', $user)) ?>
					<?php else: ?>
						–
					<?php endif ?>
					<td class="total-quantity"><?= $item->totalQuantity() ?>
					<td class="date modified">
						<time datetime="<?= $this->date->format($item->modified, 'w3c') ?>">
							<?= $this->date->format($item->modified, 'date') ?>
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

	<?=$this->view()->render(['element' => 'paging'], compact('paginator'), ['library' => 'base_core']) ?>

</article>