<?php

$this->set([
	'page' => [
		'type' => 'single',
		'title' => $item->title,
		'empty' => $t('untitled'),
		'object' => $t('product')
	],
	'meta' => [
		'is_published' => $item->is_published ? $t('published') : $t('unpublished')
	]
]);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<?=$this->form->create($item) ?>

		<div class="grid-row">
			<div class="grid-column-left">
				<?= $this->form->field('title', [
					'type' => 'text',
					'label' => $t('Title'),
					'class' => 'use-for-title'
				]) ?>
			</div>
			<div class="grid-column-right">
			</div>
		</div>

		<div class="grid-row">
			<div class="media-attachment use-media-attachment-direct">
				<?= $this->form->label('ProductsCoverMediaId', $t('Cover')) ?>
				<?= $this->form->hidden('cover_media_id') ?>
				<div class="selected"></div>
				<?= $this->html->link($t('select'), '#', ['class' => 'button select']) ?>
			</div>
		</div>

		<div class="grid-row grid-row-last">
			<div class="grid-column-left">
				<?= $this->form->field('description', [
					'type' => 'textarea',
					'label' => $t('Description'),
					'wrap' => ['class' => 'body use-editor editor-basic editor-link'],
				]) ?>
			</div>
			<div class="grid-column-right">
			</div>
		</div>

		<div class="bottom-actions">
			<?= $this->html->link($item->is_published ? $t('unpublish') : $t('publish'), ['id' => $item->id, 'action' => $item->is_published ? 'unpublish': 'publish', 'library' => 'ecommerce_core'], ['class' => 'button large']) ?>
			<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large save']) ?>
		</div>
	<?=$this->form->end() ?>
</article>