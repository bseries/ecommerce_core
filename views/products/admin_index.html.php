<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<h1 class="alpha"><?= $this->title($t('Product variants')) ?></h1>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td class="flag"><?= $t('publ.?') ?>
					<td>
					<td class="emphasize"><?= $t('Title') ?>
					<td class="emphasize"><?= $t('Number') ?>
					<td class="date created"><?= $t('Created') ?>
					<td class="actions">
			</thead>
			<tbody>
				<?php foreach ($data as $item): ?>
				<tr data-id="<?= $item->id ?>">
					<td class="flag"><?= ($item->is_published ? '✓' : '╳') ?>
					<td>
						<?php if ($cover = $item->cover()): ?>
							<?= $this->media->image($cover->version('fix3')->url('http'), ['class' => 'media']) ?>
						<?php endif ?>
					<td class="emphasize"><?= $item->title ?>
					<td class="emphasize"><?= $item->number ?: '–' ?>
					<td class="date created">
						<time datetime="<?= $this->date->format($item->created, 'w3c') ?>">
							<?= $this->date->format($item->created, 'date') ?>
						</time>
					<td class="actions">
						<?= $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
						<?= $this->html->link($item->is_published ? $t('unpublish') : $t('publish'), ['id' => $item->id, 'action' => $item->is_published ? 'unpublish': 'publish', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
						<?= $this->html->link($t('edit'), ['id' => $item->id, 'action' => 'edit', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>
</article>