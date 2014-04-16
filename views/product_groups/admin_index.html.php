<?php

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('products')
	]
]);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td class="flag"><?= $t('publ.?') ?>
					<td>
					<td class="emphasize"><?= $t('Title') ?>
					<td><?= $t('Number') ?>
					<td><?= $t('Stock') ?>
					<td class="date created"><?= $t('Created') ?>
					<td class="actions">
			</thead>
			<tbody>
				<?php foreach ($data as $item): ?>
				<tr data-id="<?= $item->id ?>">
					<td class="flag"><?= ($item->is_published ? '✓' : '×') ?>
					<td>
						<?php if ($cover = $item->cover()): ?>
							<?= $this->media->image($cover->version('fix3'), ['class' => 'media']) ?>
						<?php endif ?>
					<td class="emphasize"><?= $item->title ?>
					<td>
					<td>
					<td class="date created">
						<time datetime="<?= $this->date->format($item->created, 'w3c') ?>">
							<?= $this->date->format($item->created, 'date') ?>
						</time>
					<td class="actions">
						<?= $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'ecommerce_core'], ['class' => 'button delete']) ?>
						<?= $this->html->link($item->is_published ? $t('unpublish') : $t('publish'), ['id' => $item->id, 'action' => $item->is_published ? 'unpublish': 'publish', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
						<?= $this->html->link($t('open'), ['id' => $item->id, 'action' => 'edit', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
					<?php foreach ($item->products() as $sub): ?>
						<tr class="sub-item">
							<td class="flag"><?= ($sub->is_published ? '✓' : '×') ?>
							<td>
								<?php if ($cover = $sub->cover()): ?>
									<?= $this->media->image($cover->version('fix3'), ['class' => 'media']) ?>
								<?php endif ?>
							<td class="emphasize"><?= $sub->title ?>
							<td class="emphasize"><?= $sub->number ?>
							<td><?= $sub->stock ?>
							<td class="date created">
								<time datetime="<?= $this->date->format($sub->created, 'w3c') ?>">
									<?= $this->date->format($sub->created, 'date') ?>
								</time>
							<td class="actions">
								<?= $this->html->link($t('delete'), ['id' => $sub->id, 'controller' => 'Products', 'action' => 'delete', 'library' => 'ecommerce_core'], ['class' => 'delete button']) ?>
								<?= $this->html->link($sub->is_published ? $t('unpublish') : $t('publish'), ['id' => $sub->id, 'controller' => 'Products', 'action' => $sub->is_published ? 'unpublish': 'publish', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
								<?= $this->html->link($t('open'), ['id' => $sub->id, 'controller' => 'Products', 'action' => 'edit', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>

					<?php endforeach ?>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>
</article>