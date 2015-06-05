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
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<?=$this->form->create($item) ?>

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
				<?= $this->media->field('cover_media_id', [
					'label' => $t('Cover'),
					'attachment' => 'direct',
					'value' => $item->cover()
				]) ?>
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
			<?php if ($isTranslated): ?>
				<?php foreach ($item->translate('description') as $locale => $value): ?>
					<?= $this->editor->field("i18n.description.{$locale}", [
						'label' => $t('Description'),
						'size' => 'gamma',
						'features' => 'minimal',
						'value' => $value
					]) ?>
				<?php endforeach ?>
			<?php else: ?>
				<?= $this->editor->field('description', [
					'label' => $t('Description'),
					'size' => 'gamma',
					'features' => 'minimal'
				]) ?>
			<?php endif ?>
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