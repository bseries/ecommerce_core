<?php

use lithium\g11n\Message;
use base_core\extensions\cms\Settings;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'ecommerce_core', 'default' => $message]);
};

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('products')
	]
]);

?>
<article
	class="use-rich-index"
	data-endpoint="<?= $this->url([
		'action' => 'index',
		'page' => '__PAGE__',
		'orderField' => '__ORDER_FIELD__',
		'orderDirection' => '__ORDER_DIRECTION__',
		'filter' => '__FILTER__'
	]) ?>"
>

	<div class="top-actions">
		<?= $this->html->link($t('product'), ['action' => 'add'], ['class' => 'button add']) ?>
	</div>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td data-sort="is-published" class="flag table-sort"><?= $t('publ.?') ?>
					<td class="media">
					<td data-sort="title" class="emphasize table-sort"><?= $t('Title') ?>
					<td data-sort="number" class="emphasize number table-sort"><?= $t('Number') ?>
					<td data-sort="stock" class="stock table-sort" title="<?= $t('C = calculated, A = physically available, R = reserved, I = inventory') ?>">
						<?= $t('Stock (C/A/R/I)') ?>
					<td data-sort="modified" class="date table-sort desc"><?= $t('Modified') ?>
					<td class="actions">
						<?= $this->form->field('search', [
							'type' => 'search',
							'label' => false,
							'placeholder' => $t('Filter'),
							'class' => 'table-search',
							'value' => $this->_request->filter
						]) ?>
			</thead>
			<tbody>
				<?php foreach ($data as $item): ?>
				<tr data-id="<?= $item->id ?>">
					<td class="flag"><i class="material-icons"><?= ($item->is_published ? 'done' : '') ?></i>
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
						/
						<span class="minor"><?= $item->stock('real') ?></span>
						/
						<span class="minor"><?= $item->stock('reserved') ?></span>
						/
						<span class="minor"><?= $item->stock('target') ?></span>
					<td class="date">
						<time datetime="<?= $this->date->format($item->modified, 'w3c') ?>">
							<?= $this->date->format($item->modified, 'date') ?>
						</time>
					<td class="actions">
						<?= $this->html->link($item->is_published ? $t('unpublish') : $t('publish'), ['id' => $item->id, 'action' => $item->is_published ? 'unpublish': 'publish', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
						<?= $this->html->link($t('open'), ['id' => $item->id, 'action' => 'edit', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>

	<?=$this->view()->render(['element' => 'paging'], compact('paginator'), ['library' => 'base_core']) ?>

	<div class="bottom-help">
		<?= $t('Stock keeping consists of 4 important numbers:') ?>
		<ul>
			<li><?php echo $t('<strong>C</strong>, calculated, this is the stock amount as presented to the user. If this is 0 the item is unavailable.') ?><br>
			<li><?php echo $t('<strong>A</strong>, the physically available stock in storage.') ?>
			<li><?php echo $t('<strong>R</strong>, the reserved stock in i.e. carts.') ?>
			<li><?php echo $t('<strong>I</strong>, for note taking should equal physically available.') ?>
		</ul>
		<?php if (!Settings::read('stock.check')): ?>
			<strong>
				<?= $t('Stock checking is disabled, even if the the calculated cnumber is 0, users will be able to buy the product.') ?>
			</strong>
		<?php endif ?>
	</div>
</article>