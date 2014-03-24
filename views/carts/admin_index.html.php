<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<h1 class="alpha"><?= $this->title($t('Carts')) ?></h1>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td class="status"><?= $t('Status') ?>
					<td><?= $t('Order') ?>
					<td><?= $t('User Session ID') ?>
					<td><?= $t('User ID') ?>
					<td><?= $t('Total amount (net) ') ?>
					<td><?= $t('Total quantity') ?>
					<td class="date created"><?= $t('Created') ?>
					<td class="date modified"><?= $t('Modified') ?>
					<td>
			</thead>
			<tbody>
				<?php foreach ($data as $item): ?>
					<?php $order = $item->order() ?>
					<?php $user = $order->user() ?>
					<?php $taxZone = $user->taxZone() ?>
				<tr data-id="<?= $item->id ?>">
					<td class="status"><?= $item->status ?>
					<td><?= $this->html->link('#' .  $order->number, [
						'controller' => 'Orders', 'action' => 'edit', 'id' => $order->id,
						'library' => 'ecommerce_core'
					]) ?>
					<td><?= $item->user_session_id ?>
					<?php if ($user->isVirtual()): ?>
						<td>
							<?= $this->html->link($user->name . '/' . $user->id, [
								'controller' => 'VirtualUsers', 'action' => 'edit', 'id' => $user->id, 'library' => 'cms_core'
							]) ?>
							(<?= $this->html->link('virtual', [
								'controller' => 'VirtualUsers', 'action' => 'index', 'library' => 'cms_core'
							]) ?>)
					<?php else: ?>
						<td>
							<?= $this->html->link($user->name . '/' . $user->id, [
								'controller' => 'Users', 'action' => 'edit', 'id' => $user->id, 'library' => 'cms_core'
							]) ?>
							(<?= $this->html->link('real', [
								'controller' => 'Users', 'action' => 'index', 'library' => 'cms_core'
							]) ?>)
					<?php endif ?>
					<td><?= $this->money->format($item->totalAmount($user)->getNet(), 'money') ?>
					<td><?= $item->totalQuantity() ?>
					<td class="date created">
						<time datetime="<?= $this->date->format($item->created, 'w3c') ?>">
							<?= $this->date->format($item->created, 'date') ?>
						</time>
					<td class="date modified">
						<time datetime="<?= $this->date->format($item->modified, 'w3c') ?>">
							<?= $this->date->format($item->modified, 'date') ?>
						</time>
					<td>
						<nav class="actions">
							<?= $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
							<? // $this->html->link($t('edit'), ['id' => $item->id, 'action' => 'edit', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
						</nav>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>
</article>