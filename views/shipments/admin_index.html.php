<?php

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('shipments')
	]
]);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td class="status"><?= $t('Status') ?>
					<td><?= $t('Method') ?>
					<td><?= $t('Recipient') ?>
					<td><?= $t('Address') ?>
					<td class="date created"><?= $t('Created') ?>
					<td class="actions">
			</thead>
			<tbody>
				<?php foreach ($data as $item): ?>
					<?php $user = $item->user() ?>
				<tr data-id="<?= $item->id ?>">
					<td class="status"><?= $statuses[$item->status] ?>
					<td><?= $item->method ?>
					<td class="user">
						<?php if ($user): ?>
							<?= $this->html->link($user->number, [
								'controller' => $user->isVirtual() ? 'VirtualUsers' : 'Users',
								'action' => 'edit', 'id' => $user->id,
								'library' => 'cms_core'
							]) ?>
						<?php else: ?>
							-
						<?php endif ?>
					<td><?= $item->address()->format('oneline') ?>
					<td class="date created">
						<time datetime="<?= $this->date->format($item->created, 'w3c') ?>">
							<?= $this->date->format($item->created, 'date') ?>
						</time>
					<td class="actions">
						<?= $this->html->link($t('open'), ['id' => $item->id, 'action' => 'edit', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>
</article>