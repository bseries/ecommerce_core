<?php

use lithium\g11n\Message;
use base_core\extensions\cms\Settings;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'ecommerce_core', 'default' => $message]);
};

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
<article>
	<div class="top-actions">
		<?= $this->_render('element', 'backlink', ['type' => 'multiple'] + compact('item'), [
			'library' => 'base_core'
		]) ?>
	</div>

	<?=$this->form->create($item) ?>
		<div class="grid-row">
			<div class="grid-column-left">
				<?= $this->form->field('number', [
					'type' => 'text',
					'label' => $t('Number'),
					'placeholder' => $autoNumber ? $t('Will autogenerate number.') : null,
					'disabled' => $autoNumber && !$item->exists(),
					'readonly' => $autoNumber || $item->exists()
				]) ?>
				<div class="help">
					<?= $t('The reference number uniquely identifies this item and is used especially in correspondence with clients and customers.') ?>
				</div>
			</div>
			<div class="grid-column-right"></div>
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
			<h1><?= $t('Stock') ?></h1>

			<div class="grid-column-left">
				<?= $this->form->field('stock', [
					'type' => 'number',
					'label' => $t('Stock (physically available)'),
				]) ?>
				<div class="help">
					<?= $t('Stock that is physically available i.e. items in storage not shipped.') ?>
				</div>
				<?= $this->form->field('stock_reserved', [
					'type' => 'number',
					'label' => $t('Stock (reserved)'),
				]) ?>
				<div class="help">
					<?= $t("Items reserved because they are held in a user's cart.") ?>
				</div>
			</div>
			<div class="grid-column-right">
				<?= $this->form->field('stock_calculated', [
					'type' => 'text',
					'label' => $t('Stock (calculated)'),
					'disabled' => true,
					'value' =>  $item->stock('virtual')
				]) ?>
				<div class="help">
					<?= $t('Amount of stock as presented to users.') ?>
					<?php if (!Settings::read('stock.check')): ?>
						<strong>
							<?= $t('Stock checking is disabled, even if this number is 0, users will be able to buy the product.') ?>
						</strong>
					<?php endif ?>
					<?= $t('Calculated from: (physically available - reserved).') ?>
				</div>
				<?= $this->form->field('stock_target', [
					'type' => 'number',
					'label' => $t('Stock (inventory)'),
				]) ?>
				<div class="help">
					<?= $t('Field for inventory. Physically available stock should equal inventory.') ?>
					<?= $t('This field is for your note taking and is not used to calculate availability for users.') ?>
				</div>
			</div>
		</div>

		<div class="grid-row">
			<div class="grid-column-left">
				<?= $this->form->field('ecommerce_product_group_id', [
					'type' => 'select',
					'label' => $t('Contained in product group'),
					'list' => ['new' => '-- ' . $t('Create new product group') . ' --'] + $productGroups
				]) ?>
				<?php if ($item->exists() && $item->ecommerce_product_group_id): ?>
					<?= $this->html->link($t('open'), [
						'library' => 'ecommerce_core',
						'controller' => 'ProductGroups',
						'action' => 'edit',
						'id' => $item->ecommerce_product_group_id
					], ['class' => 'button']) ?>
				<?php endif ?>
			</div>
		</div>

		<div class="grid-row">
			<h1><?= $t('Attributes') ?></h1>
			<div class="grid-column-right use-nested">
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
			</div>
		</div>


		<div class="grid-row">
			<?php if ($isTranslated): ?>
				<?php foreach ($item->translate('description') as $locale => $value): ?>
					<?= $this->editor->field("i18n.description.{$locale}", [
						'label' => $t('Description') . ' (' . $this->g11n->name($locale) . ')',
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
			<h1 class="h-gamma"><?= $t('Prices') ?></h1>
			<?= $this->form->error('prices') ?>
			<section class="use-nested">
				<table>
					<thead>
						<tr>
							<td class="position-description--f"><?= $t('Client Group') ?>
							<td><?= $t('Acq. Method') ?>
							<td class="price-type--f"><?= $t('Tax Type') ?>
							<td class="currency--f"><?= $t('Currency') ?>
							<td class="price-type--f"><?= $t('Type') ?>
							<td class="money--f price-amount--f"><?= $t('Amount') ?>
							<td>
					</thead>
					<tbody>
					<?php foreach ($item->prices() as $key => $child): ?>
						<?php if ($key === 'new') continue ?>
						<tr class="nested-item">
							<td class="position-description--f">
								<?= $this->form->field("prices.{$key}.id", [
									'type' => 'hidden',
									'value' => $child->id,
								]) ?>
								<?= $this->form->field("prices.{$key}._delete", [
									'type' => 'hidden'
								]) ?>
								<?= $this->form->field("prices.{$key}.group", [
									'type' => 'select',
									'label' => false,
									'list' => $clientGroups,
									'value' => $child->group
								]) ?>
							<td>
								<?= $this->form->field("prices.{$key}.method", [
									'type' => 'select',
									'label' => false,
									'list' => $aquisitionMethods,
									'value' => $child->method
								]) ?>
							<td>
								<?= $this->form->field("prices.{$key}.tax_type", [
									'type' => 'select',
									'label' => false,
									'list' => $taxTypes,
									'value' => $child->tax_type
								]) ?>
							<td class="currency--f">
								<?= $this->form->field("prices.{$key}.amount_currency", [
									'type' => 'select',
									'label' => false,
									'list' => $currencies,
									'value' => $child->amount_currency
								]) ?>
							<td class="price-type--f">
								<?= $this->form->field("prices.{$key}.amount_type", [
									'type' => 'select',
									'label' => false,
									'list' => ['net' => $t('net'), 'gross' => $t('gross')],
									'value' => $child->amount_type
								]) ?>
							<td class="money--f price-amount--f">
								<?= $this->form->field("prices.{$key}.amount", [
									'type' => 'text',
									'label' => false,
									'value' => $this->money->format($child->amount, ['currency' => false]),
									'class' => 'input--money'
								]) ?>
							<td class="actions">
								<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
			<?php endforeach ?>
					<tr class="nested-add nested-item">
						<td class="position-description--f">
							<?= $this->form->field("prices.new.group", [
								'type' => 'select',
								'label' => false,
								'list' => $clientGroups
								// pick first
							]) ?>
						<td>
							<?= $this->form->field("prices.new.method", [
								'type' => 'select',
								'label' => false,
								'list' => $aquisitionMethods
								// pick first
							]) ?>
						<td>
							<?= $this->form->field("prices.new.tax_type", [
								'type' => 'select',
								'label' => false,
								'list' => $taxTypes,
								// pick first
							]) ?>
						<td class="currency--f">
							<?= $this->form->field("prices.new.amount_currency", [
								'type' => 'select',
								'label' => false,
								'list' => $currencies,
								'value' => 'EUR'
							]) ?>
						<td class="price-type--f">
							<?= $this->form->field("prices.new.amount_type", [
								'type' => 'select',
								'label' => false,
								'list' => ['net' => $t('net'), 'gross' => $t('gross')],
								'value' => 'gross'
							]) ?>
						<td class="money--f price-amount--f">
							<?= $this->form->field("prices.new.amount", [
								'type' => 'text',
								'label' => false,
								'value' => $this->money->format(0, ['currency' => false]),
								'class' => 'input--money'
							]) ?>
						<td class="actions">
							<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="7" class="nested-add-action">
								<?= $this->form->button($t('add price'), ['type' => 'button', 'class' => 'button add-nested']) ?>
					</tfoot>
				</table>
				<div class="help">
					<?= $t('There should ever be just one price for a client group/acq. method combination.') ?>
				</div>
			</section>
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