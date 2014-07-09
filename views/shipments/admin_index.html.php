<?php

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('shipments')
	]
]);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?> use-list">
	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td data-sort="number" class="number emphasize list-sort desc"><?= $t('Number') ?>
					<td data-sort="order" class="order list-sort"><?= $t('Order') ?>
					<td data-sort="status" class="status list-sort"><?= $t('Status') ?>
					<td data-sort="method" class="method list-sort"><?= $t('Method') ?>
					<td data-sort="user" class="user list-sort"><?= $t('Recipient') ?>
					<td class="address"><?= $t('Address') ?>
					<td data-sort="created" class="date created list-sort"><?= $t('Created') ?>
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
					<?php $user = $item->user() ?>
				<tr data-id="<?= $item->id ?>">
					<td class="number"><?= $item->number ?>
					<td class="order"><?= $item->order()->number ?>
					<td class="status"><?= $statuses[$item->status] ?>
					<td class="method"><?= $item->method ?>
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
					<td class="address"><?= $item->address()->format('oneline') ?>
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