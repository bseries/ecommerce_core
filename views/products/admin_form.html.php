<?php

$untitled = $t('Untitled');

$title = [
	'action' => ucfirst($this->_request->action === 'add' ? $t('creating') : $t('editing')),
	'title' => $item->title ?: $untitled,
	'object' => [ucfirst($t('order')), ucfirst($t('orders'))]
];
$this->title("{$title['title']} - {$title['object'][1]}");

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?> section-spacing">
	<h1 class="alpha">
		<span class="action"><?= $title['action'] ?></span>
		<span class="title" data-untitled="<?= $untitled ?>"><?= $title['title'] ?></span>
	</h1>

	<?=$this->form->create($item) ?>
		<?= $this->form->field('number', [
			'type' => 'text',
			'label' => $t('Number/SKU'),
		]) ?>

		<?= $this->form->field('title', ['type' => 'text', 'label' => $t('Title'), 'class' => 'use-for-title']) ?>

		<div class="media-attachment use-media-attachment-direct">
			<?= $this->form->label('ProductsCoverMediaId', $t('Cover')) ?>
			<?= $this->form->hidden('cover_media_id') ?>
			<div class="selected"></div>
			<?= $this->html->link($t('select'), '#', ['class' => 'button select']) ?>
		</div>
		<div class="media-attachment use-media-attachment-joined">
			<?= $this->form->label('ProductsMedia', $t('Media')) ?>
			<?php foreach ($item->media() as $media): ?>
				<?= $this->form->hidden('media.' . $media->id . '.id', ['value' => $media->id]) ?>
			<?php endforeach ?>

			<div class="selected"></div>
			<?= $this->html->link($t('select'), '#', ['class' => 'button select']) ?>
		</div>

		<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large']) ?>

	<?=$this->form->end() ?>
</article>