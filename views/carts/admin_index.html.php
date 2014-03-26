<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?> use-list">
	<h1 class="alpha"><?= $this->title($t('Carts')) ?></h1>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td data-sort="status" class="status list-sort"><?= $t('Status') ?>
					<td data-sort="order" class="order list-sort"><?= $t('Order') ?>
					<td data-sort="user" class="user list-sort"><?= $t('User') ?>
					<td><?= $t('User Session ID') ?>
					<td><?= $t('Total amount (net) ') ?>
					<td><?= $t('Total quantity') ?>
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
					<?php $user = $order->user() ?>
					<?php $taxZone = $user->taxZone() ?>
				<tr data-id="<?= $item->id ?>">
					<td class="status"><?= $item->status ?>
					<td class="order"><?= $this->html->link($order->number, [
						'controller' => 'Orders', 'action' => 'edit', 'id' => $order->id,
						'library' => 'ecommerce_core'
					]) ?>
					<td class="user">
					<?php if ($user): ?>
						<?= $this->html->link($user->title(), [
							'controller' => $user->isVirtual() ? 'VirtualUsers' : 'Users',
							'action' => 'edit', 'id' => $user->id,
							'library' => 'cms_core'
						]) ?>
					<?php else: ?>
						-
					<?php endif ?>
					<td><?= $item->user_session_id ?>
					<td><?= $this->money->format($item->totalAmount($user, $user->taxZone())->getNet(), 'money') ?>
					<td><?= $item->totalQuantity() ?>
					<td class="date created">
						<time datetime="<?= $this->date->format($item->created, 'w3c') ?>">
							<?= $this->date->format($item->created, 'date') ?>
						</time>
					<td class="actions">
						<?php // $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
						<?php // $this->html->link($t('edit'), ['id' => $item->id, 'action' => 'edit', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>
</article>