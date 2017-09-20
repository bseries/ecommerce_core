<?php

use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'ecommerce_core', 'default' => $message]);
};

$this->set([
	'page' => [
		'type' => 'single',
		'title' => $item->title,
		'empty' => $t('untitled'),
		'object' => $t('product group')
	],
	'meta' => [
		'is_promoted' => $item->is_promoted ? $t('promoted') : $t('unpromoted'),
		'is_published' => $item->is_published ? $t('published') : $t('unpublished')
	]
]);

?>
<article>
	<div class="top-actions">
		<?= $this->_render('element', 'backlink', ['type' => 'multiple'] + compact('item'), [
			'library' => 'base_core'
		]) ?>
	</div>

	<?=$this->form->create($item) ?>

		<div class="grid-row">
			<h1><?= $t('Access') ?></h1>
			<div class="grid-column-left"></div>
			<div class="grid-column-right">
				<?= $this->form->field('access', [
					'type' => 'select',
					'multiple' => true,
					'list' => $rules,
					'label' => $t('Visible for…'),
				]) ?>
			</div>
		</div>

		<div class="grid-row">
			<div class="grid-column-left">
				<?php if ($isTranslated): ?>
					<?php foreach ($item->translate('title') as $locale => $value): ?>
						<?= $this->form->field("i18n.title.{$locale}", [
							'type' => 'text',
							'label' => $t('Title') . ' (' . $this->g11n->name($locale) . ')',
							'class' => $locale === PROJECT_LOCALE ? 'use-for-title' : null,
							'value' => $value
						]) ?>
					<?php endforeach ?>
				<?php else: ?>
					<?= $this->form->field('title', [
						'type' => 'text',
						'label' => $t('Title'),
						'class' => 'use-for-title'
					]) ?>
				<?php endif ?>
			</div>
			<div class="grid-column-right">
				<?= $this->form->field('tags', [
					'value' => $item->tags(),
					'label' => $t('Tags'),
					'placeholder' => 'foo, bar',
					'class' => 'input--tags'
				]) ?>
				<?php if (isset($brands)): ?>
					<?= $this->form->field('ecommerce_brand_id', [
						'type' => 'select',
						'list' => [null => null] + $brands,
						'label' => $t('Brand')
					]) ?>
				<?php endif ?>
			</div>
		</div>

		<div class="grid-row">
			<div class="grid-column-left">
				<?= $this->media->field('cover_media_id', [
					'label' => $t('Cover'),
					'attachment' => 'direct',
					'value' => $item->cover()
				]) ?>
			</div>
			<div class="grid-column-right">
				<?= $this->media->field('media', [
					'label' => $t('Media'),
					'attachment' => 'joined',
					'value' => $item->media()
				]) ?>
			</div>
		</div>

		<div class="grid-row">
			<?php if ($isTranslated): ?>
				<?php foreach ($item->translate('description') as $locale => $value): ?>
					<?= $this->editor->field("i18n.description.{$locale}", [
						'label' => $t('Description'),
						'size' => 'beta',
						'features' => 'full',
						'value' => $value
					]) ?>
				<?php endforeach ?>
			<?php else: ?>
				<?= $this->editor->field('description', [
					'label' => $t('Description'),
					'size' => 'beta',
					'features' => 'full'
				]) ?>
			<?php endif ?>
		</div>

		<div class="grid-row">
			<h1><?= $t('Products') ?></h1>
			<?php if ($item->products()->count()): ?>
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
					</thead>
					<tbody>
						<?php foreach ($item->products() as $_item): ?>
						<tr data-id="<?= $_item->id ?>">
							<td class="flag"><i class="material-icons"><?= ($_item->is_published ? 'done' : '') ?></i>
							<td class="media">
								<?php if ($cover = $_item->cover()): ?>
									<?= $this->media->image($cover->version('fix3admin'), [
										'data-media-id' => $cover->id, 'alt' => 'preview'
									]) ?>
								<?php endif ?>
							<td class="emphasize title"><?= $_item->title ?>
							<td class="emphasize number"><?= $_item->number ?: '–' ?>
							<td class="emphasize stock">
								<span><?= $_item->stock('virtual') ?></span>
								/
								<span class="minor"><?= $_item->stock('real') ?></span>
								/
								<span class="minor"><?= $_item->stock('reserved') ?></span>
								/
								<span class="minor"><?= $_item->stock('target') ?></span>
							<td class="date">
								<time datetime="<?= $this->date->format($_item->modified, 'w3c') ?>">
									<?= $this->date->format($_item->modified, 'date') ?>
								</time>
							<td class="actions">
								<?= $this->html->link($_item->is_published ? $t('unpublish') : $t('publish'), [
									'library' => 'ecommerce_core',
									'controller' => 'Products',
									'action' => $_item->is_published ? 'unpublish': 'publish',
									'id' => $_item->id
								], ['class' => 'button']) ?>
								<?= $this->html->link($t('open'), [
									'library' => 'ecommerce_core',
									'controller' => 'Products',
									'action' => 'edit',
									'id' => $_item->id
								], ['class' => 'button']) ?>
						<?php endforeach ?>
					</tbody>
				</table>
			<?php else: ?>
				<div class="none-available"><?= $t('No items available, yet.') ?></div>
			<?php endif ?>
		</div>

		<div class="bottom-actions">
			<div class="bottom-actions__left">
				<?php if ($item->exists()): ?>
					<?= $this->html->link($t('delete'), [
						'action' => 'delete', 'id' => $item->id
					], ['class' => 'button large delete']) ?>
				<?php endif ?>
			</div>
			<div class="bottom-actions__right">
				<?php if ($item->exists()): ?>
					<?= $this->html->link($item->is_promoted ? $t('unpromote') : $t('promote'), ['id' => $item->id, 'action' => $item->is_promoted ? 'unpromote': 'promote'], ['class' => 'button large']) ?>
					<?= $this->html->link($item->is_published ? $t('unpublish') : $t('publish'), ['id' => $item->id, 'action' => $item->is_published ? 'unpublish': 'publish'], ['class' => 'button large']) ?>
				<?php endif ?>
				<?= $this->form->button($t('save'), [
					'type' => 'submit',
					'class' => 'button large save'
				]) ?>
			</div>
		</div>

	<?=$this->form->end() ?>
</article>