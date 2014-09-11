<?php

$this->set([
	'page' => [
		'type' => 'single',
		'title' => $item->title,
		'empty' => $t('untitled'),
		'object' => $t('products')
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
				<?= $this->form->field('number', [
					'type' => 'text',
					'label' => $t('Number/SKU'),
				]) ?>
			</div>
			<div class="grid-column-right">
				<?= $this->form->field('stock', [
					'type' => 'number',
					'label' => $t('Stock'),
				]) ?>
				<div class="help">
					<?= $t('Updates real stock.') ?>
					<?php if ($item->exists()): ?>
						<?= $t('Current real stock is {:realCount}, current virtual stock is {:virtualCount}.', [
							'realCount' => $item->stock('real'),
							'virtualCount' => $item->stock('virtual')
						]) ?>
					<?php endif ?>
				</div>
				<?= $this->form->field('ecommerce_product_group_id', [
					'type' => 'select',
					'label' => $t('Contained in product group'),
					'list' => ['new' => '-- ' . $t('Create new product group') . ' --'] + $productGroups
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
				<div class="media-attachment use-media-attachment-direct">
					<?= $this->form->label('ProductsCoverMediaId', $t('Cover')) ?>
					<?= $this->form->hidden('cover_media_id') ?>
					<div class="selected"></div>
					<?= $this->html->link($t('select'), '#', ['class' => 'button select']) ?>
				</div>
			</div>
			<div class="grid-column-right">
				<section class="use-nested">
					<h1><?= $t('Attributes') ?></h1>
					<table>
						<thead>
							<tr>
								<td><?= $t('Name') ?>
								<td><?= $t('Value') ?>
								<td>
						</thead>
						<tbody>
						<?php foreach ($item->attributes() as $key => $child): ?>
							<tr class="nested-item">
								<td>
									<?= $this->form->field("attributes.{$key}.id", [
										'type' => 'hidden',
										'value' => $child->id,
									]) ?>
									<?= $this->form->field("attributes.{$key}.key", [
										'type' => 'select',
										'value' => $child->key,
										'list' => $attributeKeys,
										'label' => false,
									]) ?>
								<td>
									<?= $this->form->field("attributes.{$key}.value", [
										'type' => 'text',
										'value' => $child->value,
										'label' => false
									]) ?>
								<td class="actions">
									<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
						<?php endforeach ?>
							<tr class="nested-add nested-item">
								<td>
									<?= $this->form->field('attributes.new.key', [
										'type' => 'select',
										'list' => $attributeKeys,
										'label' => false,
									]) ?>
								<td>
									<?= $this->form->field('attributes.new.value', [
										'type' => 'text',
										'label' => false
									]) ?>
								<td class="actions">
									<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="3" class="nested-add-action">
									<?= $this->form->button($t('add'), ['type' => 'button', 'class' => 'button add-nested']) ?>
						</tfoot>
					</table>
				</section>
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
							<td><?= $t('Tax type') ?>
							<td><?= $t('Tax rate (%)') ?>
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
									'value' => $child->amount_type
								]) ?>
							<td>
								<?= $this->form->field("prices.{$key}.price_currency", [
									'type' => 'select',
									'label' => false,
									// 'disabled' => true,
									'list' => $currencies,
									'value' => $child->amount_currency
								]) ?>
							<td>
								<?= $this->form->field("prices.{$key}.price", [
									'type' => 'text',
									'label' => false,
									'value' => $this->money->format($child->amount, 'decimal')
								]) ?>
							<td>
								<?= $this->form->field("prices.{$key}.tax_type", [
									'type' => 'select',
									'label' => false,
									'list' => $taxeTypes,
									// 'disabled' => true,
									'value' => $child->tax_type
								]) ?>
							<td>
								<?= $this->form->field("prices.{$key}.tax_rate", [
									'type' => 'text',
									// 'disabled' => true,
									'value' => $child->tax_rate,
									'label' => false
								]) ?>
							<td>
					<?php endforeach ?>
				</table>
			</section>
		</div>

		<div class="bottom-actions">
			<?php if ($item->exists()): ?>
				<?= $this->html->link($item->is_published ? $t('unpublish') : $t('publish'), ['id' => $item->id, 'action' => $item->is_published ? 'unpublish': 'publish', 'library' => 'ecommerce_core'], ['class' => 'button large']) ?>
			<?php endif ?>
			<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large save']) ?>
		</div>
	<?=$this->form->end() ?>
</article>