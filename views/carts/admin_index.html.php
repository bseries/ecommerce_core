<?php

use lithium\g11n\Message;
use base_core\extensions\cms\Settings;

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
					<td data-sort="status" class="status table-sort"><?= $t('Status') ?>
					<td data-sort="Order.number" class="order table-sort"><?= $t('Order') ?>
					<td data-sort="User.number" class="user table-sort"><?= $t('User') ?>
					<td class="money"><?= $t('Total value (net) ') ?>
					<td data-sort="total-quantity" class="total-quantity table-sort"><?= $t('Total quantity') ?>
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
						<?= $this->user->link($user) ?>
					<td class="total-amount money">
					<?php if ($user): ?>
						<?= $this->money->format($item->totalValues('net', $user)) ?>
					<?php else: ?>
						–
					<?php endif ?>
					<td class="total-quantity"><?= $item->totalQuantity() ?>
					<td class="date modified">
						<time datetime="<?= $this->date->format($item->modified, 'w3c') ?>">
							<?= $this->date->format($item->modified, 'date') ?>
						</time>
					<?php if ($useSites): ?>
						<td>
							<?= $item->site ?: '-' ?>
					<?php endif ?>
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

	<?=$this->_render('element', 'paging', compact('paginator'), ['library' => 'base_core']) ?>

	<div class="bottom-help">
		<?= $t('When a cart is created all contained items are marked as reserved.') ?>
		<?php if ($count = Settings::read('cart.limitItemsPerPosition')): ?>
			<strong><?= $t('Cart item limiting is enabled.') ?></strong>
			<?= $t('A maximum of {:count} items can be hold per cart.', compact('count')) ?>
		<?php endif ?>
	</div>

</article>
