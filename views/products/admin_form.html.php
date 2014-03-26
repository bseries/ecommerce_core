<?php

$untitled = $t('Untitled');

$title = [
	'action' => ucfirst($this->_request->action === 'add' ? $t('creating') : $t('editing')),
	'title' => $item->title ?: $untitled,
	'object' => [ucfirst($t('product variant')), ucfirst($t('product variants'))]
];
$this->title("{$title['title']} - {$title['object'][1]}");

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?> section-spacing">
	<h1 class="alpha">
		<span class="action"><?= $title['action'] ?></span>
		<span class="object"><?= $title['object'][0] ?></span>
		<span class="title" data-untitled="<?= $untitled ?>"><?= $title['title'] ?></span>
		<span class="status"><?= $item->is_published ? $t('published') : $t('unpublished') ?></span>
	</h1>

	<nav class="actions">
		<?= $this->html->link($item->is_published ? $t('unpublish') : $t('publish'), ['id' => $item->id, 'action' => $item->is_published ? 'unpublish': 'publish', 'library' => 'ecommerce_core'], ['class' => 'button']) ?>
	</nav>


	<?=$this->form->create($item) ?>
		<?= $this->form->field('number', [
			'type' => 'text',
			'label' => $t('Number/SKU'),
		]) ?>

		<?= $this->form->field('title', [
			'type' => 'text',
			'label' => $t('Title'),
			'class' => 'use-for-title'
		]) ?>

		<?= $this->form->field('ecommerce_product_group_id', [
			'type' => 'select',
			'label' => $t('Contained in product group'),
			'list' => ['new' => '-- ' . $t('Create new product group') . ' --'] + $productGroups
		]) ?>

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

		<?= $this->form->field('description', [
			'type' => 'textarea',
			'label' => $t('Description'),
			'wrap' => ['class' => 'body use-editor editor-basic editor-link'],
		]) ?>

		<section class="use-nested">
			<h1 class="beta"><?= $t('Prices') ?></h1>
			<table>
				<thead>
					<tr>
						<td><?= $t('Name') ?>
						<td><?= $t('Type') ?>
						<td><?= $t('Currency') ?>
						<td><?= $t('Amount') ?>
						<td>
				</thead>
				<tbody>
				<?php foreach ($item->prices() as $key => $child): ?>
					<tr class="nested-item">
						<td>
							<?= $this->form->field("prices.{$key}.id", [
								'type' => 'hidden',
								'value' => $child->id,
							]) ?>
							<?= $this->form->field("prices.{$key}.group", [
								'type' => 'hidden',
								'value' => $child->group,
							]) ?>
							<?= $this->form->field("prices.{$key}.group.title", [
								'type' => 'text',
								'disabled' => true,
								'value' => $child->group()->title,
								'label' => false
							]) ?>
						<td>
							<?= $this->form->field("prices.{$key}.price_type", [
								'type' => 'select',
								'label' => false,
								'list' => ['net' => $t('net'), 'gross' => $t('gross')],
								'value' => $child->price_type
							]) ?>
						<td>
							<?= $this->form->field("prices.{$key}.price_currency", [
								'type' => 'select',
								'label' => false,
								'list' => $currencies,
								'value' => $child->price_currency
							]) ?>
						<td>
							<?= $this->form->field("prices.{$key}.price", [
								'type' => 'text',
								'label' => false,
								'value' => $this->money->format($child->price, 'decimal')
							]) ?>
						<td>
				<?php endforeach ?>
			</table>
		</section>

		<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large']) ?>

	<?=$this->form->end() ?>
</article>