<?php

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('products')
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

	<div class="top-actions">
		<?= $this->html->link($t('new product'), ['action' => 'add'], ['class' => 'button add']) ?>
	</div>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td data-sort="is-published" class="flag table-sort"><?= $t('publ.?') ?>
					<td class="media">
					<td data-sort="title" class="emphasize table-sort"><?= $t('Title') ?>
					<td data-sort="number" class="emphasize number table-sort"><?= $t('Number') ?>
					<td data-sort="stock" class="stock table-sort"><?= $t('Stock') ?>
					<td data-sort="modified" class="date table-sort desc"><?= $t('Modified') ?>
					<td class="actions">
			</thead>
			<tbody class="list">
				<?php foreach ($data as $item): ?>
				<tr data-id="<?= $item->id ?>">
					<td class="flag"><?= ($item->is_published ? '✓' : '×') ?>
					<td class="media">
						<?php if ($cover = $item->cover()): ?>
							<?= $this->media->image($cover->version('fix3admin'), [
								'data-media-id' => $cover->id, 'alt' => 'preview'
							]) ?>
						<?php endif ?>
					<td class="emphasize title"><?= $item->title ?>
					<td class="emphasize number"><?= $item->number ?: '–' ?>
					<td class="emphasize stock">
						<span><?= $item->stock('virtual') ?></span>
						<span class="minor"><?= $item->stock('real') ?></span>
					<td class="date">
						<time datetime="<?= $this->date->format($item->modified, 'w3c') ?>">
							<?= $this->date->format($item->modified, 'date') ?>
						</time>
					<td class="actions">
						<?= $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'ecommerce_core'], ['class' => 'delete button']) ?>
						<?= $this->html->link($item->is_published ? $t('unpublish') : $t('publish'), ['id' => $item->id, 'action' => $item->is_published ? 'unpublish': 'publish', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
						<?= $this->html->link($t('open'), ['id' => $item->id, 'action' => 'edit', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>

	<?=$this->view()->render(['element' => 'paging'], compact('paginator'), ['library' => 'base_core']) ?>
</article>