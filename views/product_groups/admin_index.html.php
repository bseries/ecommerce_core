<?php

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('product groups')
	]
]);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?> use-list">

	<div class="top-actions">
		<?= $this->html->link($t('new product group'), ['action' => 'add', 'library' => 'ecommerce_core'], ['class' => 'button add']) ?>
	</div>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td data-sort="is-published" class="flag is-published list-sort"><?= $t('publ.?') ?>
					<td data-sort="is-promoted" class="flag is-promoted list-sort"><?= $t('prom.?') ?>
					<td class="media">
					<td data-sort="title" class="emphasize title list-sort"><?= $t('Title') ?>
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
				<tr data-id="<?= $item->id ?>">
					<td class="flag is-published"><?= ($item->is_published ? '✓' : '×') ?>
					<td class="flag is-promoted"><?= ($item->is_promoted ? '✓' : '×') ?>
					<td class="media">
						<?php if ($cover = $item->cover()): ?>
							<?= $this->media->image($cover->version('fix3admin'), [
								'data-media-id' => $cover->id, 'alt' => 'preview'
							]) ?>
						<?php endif ?>
					<td class="emphasize title"><?= $item->title ?>
					<td class="date created">
						<time datetime="<?= $this->date->format($item->created, 'w3c') ?>">
							<?= $this->date->format($item->created, 'date') ?>
						</time>
					<td class="actions">
						<?= $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'ecommerce_core'], ['class' => 'button delete']) ?>
						<?= $this->html->link($item->is_promoted ? $t('unpromote') : $t('promote'), ['id' => $item->id, 'action' => $item->is_promoted ? 'unpromote': 'promote', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
						<?= $this->html->link($item->is_published ? $t('unpublish') : $t('publish'), ['id' => $item->id, 'action' => $item->is_published ? 'unpublish': 'publish', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
						<?= $this->html->link($t('open'), ['id' => $item->id, 'action' => 'edit', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>
</article>