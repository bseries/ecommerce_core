<?php

$this->set([
	'page' => [
		'type' => 'single',
		'title' => $item->title,
		'empty' => $t('untitled'),
		'object' => $t('product variant')
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
				<?= $this->form->field('number', [
					'type' => 'text',
					'label' => $t('Number/SKU'),
				]) ?>
				<?= $this->form->field('ecommerce_product_group_id', [
					'type' => 'select',
					'label' => $t('Contained in product group'),
					'list' => ['new' => '-- ' . $t('Create new product group') . ' --'] + $productGroups
				]) ?>
			</div>
		</div>

		<div class="grid-row">
			<div class="grid-column-left">
				<div class="media-attachment use-media-attachment-direct">
					<?= $this->form->label('ProductsCoverMediaId', $t('Cover')) ?>
					<?= $this->form->hidden('cover_media_id') ?>
					<div class="selected"></div>
					<?= $this->html->link($t('select'), '#', ['class' => 'button select']) ?>
				</div>
			</div>
			<div class="grid-column-right">
			</div>
		</div>

		<div class="grid-row">
			<div class="grid-column-left">
				<?= $this->form->field('description', [
					'type' => 'textarea',
					'label' => $t('Description'),
					'wrap' => ['class' => 'body use-editor editor-basic editor-link'],
				]) ?>
			</div>
			<div class="grid-column-right">
				<div class="media-attachment use-media-attachment-joined">
					<?= $this->form->label('ProductsMedia', $t('Media')) ?>
					<?php foreach ($item->media() as $media): ?>
						<?= $this->form->hidden('media.' . $media->id . '.id', ['value' => $media->id]) ?>
					<?php endforeach ?>

					<div class="selected"></div>
					<?= $this->html->link($t('select'), '#', ['class' => 'button select']) ?>
				</div>
			</div>
		</div>

		<div class="grid-row grid-row-last">
			<section class="use-nested">
				<h1 class="h-gamma"><?= $t('Prices') ?></h1>
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
		</div>

		<div class="bottom-actions">
			<?= $this->html->link($item->is_published ? $t('unpublish') : $t('publish'), ['id' => $item->id, 'action' => $item->is_published ? 'unpublish': 'publish', 'library' => 'ecommerce_core'], ['class' => 'button large']) ?>
			<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large save']) ?>
		</div>
	<?=$this->form->end() ?>
</article>