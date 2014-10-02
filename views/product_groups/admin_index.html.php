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
					<td>
					<td data-sort="title" class="emphasize title list-sort"><?= $t('Title') ?>
					<td class="number"><?= $t('Number') ?>
					<td class="stock"><?= $t('Stock') ?>
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
					<td>
						<?php if ($cover = $item->cover()): ?>
							<?= $this->media->image($cover->version('fix3'), ['class' => 'media']) ?>
						<?php endif ?>
					<td class="emphasize title"><?= $item->title ?>
					<td>
					<td>
					<td class="date created">
						<time datetime="<?= $this->date->format($item->created, 'w3c') ?>">
							<?= $this->date->format($item->created, 'date') ?>
						</time>
					<td class="actions">
						<?= $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'ecommerce_core'], ['class' => 'button delete']) ?>
						<?= $this->html->link($item->is_promoted ? $t('unpromote') : $t('promote'), ['id' => $item->id, 'action' => $item->is_promoted ? 'unpromote': 'promote', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
						<?= $this->html->link($item->is_published ? $t('unpublish') : $t('publish'), ['id' => $item->id, 'action' => $item->is_published ? 'unpublish': 'publish', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
						<?= $this->html->link($t('open'), ['id' => $item->id, 'action' => 'edit', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
					<?php foreach ($item->products() as $sub): ?>
						<tr class="sub-item">
							<td class="flag"><?= ($sub->is_published ? '✓' : '×') ?>
							<td>
							<td>
								<?php if ($cover = $sub->cover()): ?>
									<?= $this->media->image($cover->version('fix3'), ['class' => 'media']) ?>
								<?php endif ?>
							<td class="emphasize"><?= $sub->title ?>
							<td class="emphasize"><?= $sub->number ?>
							<td>
								<span><?= $sub->stock('virtual') ?></span>
								<span class="minor"><?= $sub->stock('real') ?></span>
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