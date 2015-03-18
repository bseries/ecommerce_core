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
					<td data-sort="number" class="number emphasize table-sort"><?= $t('Number') ?>
					<td data-sort="order" class="order table-sort"><?= $t('Order') ?>
					<td data-sort="status" class="status table-sort"><?= $t('Status') ?>
					<td data-sort="method" class="method table-sort"><?= $t('Method') ?>
					<td data-sort="user.number" class="user table-sort"><?= $t('Recipient') ?>
					<td><?= $t('Total (net)') ?>
					<td data-sort="modified" class="date modified table-sort desc"><?= $t('Modified') ?>
					<td class="actions">
			</thead>
			<tbody>
				<?php foreach ($data as $item): ?>
					<?php $user = $item->user() ?>
					<?php $order = $item->order() ?>
				<tr data-id="<?= $item->id ?>">
					<td class="number"><?= $item->number ?>
					<td class="order"><?= $this->html->link($order->number, [
						'controller' => 'Orders', 'action' => 'edit', 'id' => $order->id,
						'library' => 'ecommerce_core'
					]) ?>
					<td class="status"><?= $statuses[$item->status] ?>
					<td class="method"><?= $item->method ?>
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
					<td><?= $this->price->format($item->totals(), 'net') ?>
					<td class="date modified">
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