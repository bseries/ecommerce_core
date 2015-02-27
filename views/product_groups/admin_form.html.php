<?php

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
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<?=$this->form->create($item) ?>

		<div class="grid-row">
			<div class="grid-column-left">
				<?php if ($isTranslated): ?>
					<?php foreach ($item->translate('title') as $locale => $value): ?>
						<?= $this->form->field("i18n.title.{$locale}", [
							'type' => 'text',
							'label' => $t('Title') . ' (' . $this->g11n->name($locale) . ')',
							'class' => 'use-for-title',
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
				<div class="media-attachment use-media-attachment-direct">
					<?= $this->form->label('ProductsCoverMediaId', $t('Cover')) ?>
					<?= $this->form->hidden('cover_media_id') ?>
					<div class="selected"></div>
					<?= $this->html->link($t('select'), '#', ['class' => 'button select']) ?>
				</div>
			</div>
		</div>

		<div class="grid-row">
			<div class="grid-column-left">
			</div>
			<div class="grid-column-right">
				<?= $this->form->field('access', [
					'type' => 'select',
					'multiple' => true,
					'list' => $rules,
					'label' => $t('Access'),
				]) ?>
			</div>
		</div>

		<div class="grid-row">
			<div class="grid-column-left">
				<?php if (isset($brands)): ?>
					<?= $this->form->field('ecommerce_brand_id', [
						'type' => 'select',
						'list' => [null => null] + $brands,
						'label' => $t('Brand')
					]) ?>
				<?php endif ?>
			</div>
			<div class="grid-column-right">
				<?= $this->form->field('tags', ['value' => $item->tags(), 'label' => $t('Tags'), 'placeholder' => 'foo, bar']) ?>
			</div>
		</div>

		<div class="grid-row">
			<div class="grid-column-left">
				<?php if ($isTranslated): ?>
					<?php foreach ($item->translate('description') as $locale => $value): ?>
						<?= $this->form->field("i18n.description.{$locale}", [
							'type' => 'textarea',
							'label' => $t('Text') . ' (' . $this->g11n->name($locale) . ')',
							'wrap' => ['class' => 'editor-size--gamma use-editor editor-basic editor-link'],
							'value' => $value
						]) ?>
					<?php endforeach ?>
				<?php else: ?>
					<?= $this->form->field('description', [
						'type' => 'textarea',
						'label' => $t('Text'),
						'wrap' => ['class' => 'editor-size--gamma use-editor editor-basic editor-link']
					]) ?>
				<?php endif ?>
			</div>
			<div class="grid-column-right">
			</div>
		</div>

		<div class="bottom-actions">
			<?php if ($item->exists()): ?>
				<?= $this->html->link($item->is_promoted ? $t('unpromote') : $t('promote'), ['id' => $item->id, 'action' => $item->is_promoted ? 'unpromote': 'promote', 'library' => 'ecommerce_core'], ['class' => 'button large']) ?>
				<?= $this->html->link($item->is_published ? $t('unpublish') : $t('publish'), ['id' => $item->id, 'action' => $item->is_published ? 'unpublish': 'publish', 'library' => 'ecommerce_core'], ['class' => 'button large']) ?>
			<?php endif ?>
			<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large save']) ?>
		</div>
	<?=$this->form->end() ?>
</article>