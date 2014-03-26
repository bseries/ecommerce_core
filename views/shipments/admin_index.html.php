<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<h1 class="alpha"><?= $this->title($t('Shipments')) ?></h1>

	<?php if ($data->count()): ?>
		<table>

			<thead>
				<tr>
					<td class="status"><?= $t('Status') ?>
					<td><?= $t('User') ?>
					<td><?= $t('Method') ?>
					<td><?= $t('Address') ?>
					<td class="date created"><?= $t('Created') ?>
					<td class="actions">
			</thead>
			<tbody>
				<?php foreach ($data as $item): ?>
					<?php $user = $item->user() ?>
				<tr data-id="<?= $item->id ?>">
					<td class="status"><?= $item->status ?>
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
					<td><?= $item->method ?>
					<td><?= $item->address()->format('oneline') ?>
					<td class="date created">
						<time datetime="<?= $this->date->format($item->created, 'w3c') ?>">
							<?= $this->date->format($item->created, 'date') ?>
						</time>
					<td class="actions">
						<?= $this->html->link($t('edit'), ['id' => $item->id, 'action' => 'edit', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>
</article>