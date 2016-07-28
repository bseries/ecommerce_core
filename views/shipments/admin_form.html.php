<?php

use base_core\extensions\cms\Settings;
use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'ecommerce_core', 'default' => $message]);
};

$this->set([
	'page' => [
		'type' => 'single',
		'title' => $item->number,
		'empty' => false,
		'object' => $t('shipment')
	],
	'meta' => [
		'status' => $statuses[$item->status]
	]
]);

?>
<article>
	<?=$this->form->create($item) ?>
		<?php if ($item->exists()): ?>
			<?= $this->form->field('id', ['type' => 'hidden']) ?>
		<?php endif ?>

		<div class="grid-row">
			<div class="grid-column-left">
				<?= $this->form->field('number', [
					'type' => 'text',
					'label' => $t('Number'),
					'class' => 'use-for-title'
				]) ?>
				<div class="help"><?= $t('Leave empty to autogenerate number.') ?></div>
			</div>
			<div class="grid-column-right">
				<?= $this->form->field('status', [
					'type' => 'select',
					'label' => $t('Status'),
					'list' => $statuses
				]) ?>
				<div class="help">
					<?= $t('When shipment is set to `shipped` the item stock will be moved from reserved to taken, and acutally be subtracted from physically available stock.') ?>
					<?php if (Settings::read('shipment.sendShippedMail')): ?>
						<strong>
							<?= $t('The user will be notified by e-mail when the status is changed to `shipped`.') ?>
						</strong>
					<?php endif ?>
				</div>

				<?= $this->form->field('created', [
					'label' => $t('Created'),
					'disabled' => true,
					'value' => $this->date->format($item->created, 'datetime')
				]) ?>
			</div>
		</div>

		<div class="grid-row">
			<h1 class="h-gamma"><?= $t('User') ?></h1>
			<div class="grid-column-left">
				<?= $this->form->field('user_id', [
					'type' => 'select',
					'label' => $t('User'),
					'list' => $users,
					'disabled' => $item->exists()
				]) ?>

			</div>
			<?php if ($user = $item->user()): ?>
			<div class="grid-column-right">
				<?= $this->form->field('user.number', [
					'label' => $t('Number'),
					'disabled' => true,
					'value' => $user->number
				]) ?>
				<?= $this->form->field('user.name', [
					'label' => $t('Name'),
					'disabled' => true,
					'value' => $user->name
				]) ?>
				<?= $this->form->field('user.email', [
					'label' => $t('Email'),
					'disabled' => true,
					'value' => $user->email
				]) ?>
			</div>
			<div class="actions">
				<?= $this->html->link($t('open user'), [
					'controller' => 'Users',
					'action' => 'edit',
					'id' => $user->id,
					'library' => 'base_core'
				], ['class' => 'button']) ?>
			</div>
			<?php endif ?>
		</div>

		<div class="grid-row">
			<h1 class="h-gamma"><?= $t('Recipient') ?></h1>

			<div class="grid-column-left">
				<?= $this->form->field('address', [
					'type' => 'textarea',
					'label' => $t('Receiving Address'),
					'disabled' => true,
					'value' => $item->address()->format('postal', $locale)
				]) ?>
			</div>
			<div class="grid-column-right">
				<?= $this->form->field('method', [
					'type' => 'select',
					'label' => $t('Method'),
					'list' => $methods
				]) ?>
				<?= $this->form->field('tracking', [
					'label' => $t('Tracking Number'),
				]) ?>
				<div class="help"><?= $t('Tracking is available once status is `shipped`.') ?></div>

			</div>
		</div>

		<div class="grid-row">
			<section class="grid-column-left">
				<?= $this->form->field('terms', [
					'type' => 'textarea',
					'label' => $t('Terms')
				]) ?>
				<div class="help"><?= $t('Visible to recipient.') ?></div>
			</section>
			<section class="grid-column-right">
				<?= $this->form->field('note', [
					'type' => 'textarea',
					'label' => $t('Note')
				]) ?>
				<div class="help"><?= $t('Visible to recipient.') ?></div>
			</section>
		</div>

		<div class="grid-row">
			<h1 class="h-gamma"><?= $t('Positions') ?></h1>
			<section class="use-nested">
				<table>
					<thead>
						<tr>
							<td><?= $t('Description') ?>
							<td><?= $t('Quantity') ?>
							<td><?= $t('Currency') ?>
							<td><?= $t('Type') ?>
							<td class="money--f price-amount--f"><?= $t('Unit value') ?>
							<td class="money--f position-total--f"><?= $t('Line total value (net)') ?>
							<td class="actions">
					</thead>
					<tbody>
					<?php foreach ($item->positions() as $key => $child): ?>
						<tr class="nested-item">
							<td>
								<?= $this->form->field("positions.{$key}.id", [
									'type' => 'hidden',
									'value' => $child->id
								]) ?>
								<?= $this->form->field("positions.{$key}._delete", [
									'type' => 'hidden'
								]) ?>
								<?= $this->form->field("positions.{$key}.description", [
									'type' => 'text',
									'label' => false,
									'value' => $child->description
								]) ?>
							<td>
								<?= $this->form->field("positions.{$key}.quantity", [
									'type' => 'text',
									'label' => false,
									'value' => $this->number->format($child->quantity, 'decimal')
								]) ?>
							<td>
								<?= $this->form->field("positions.{$key}.amount_currency", [
									'type' => 'select',
									'label' => false,
									'list' => $currencies,
									'value' => $child->amount_currency
								]) ?>
							<td>
								<?= $this->form->field("positions.{$key}.amount_type", [
									'type' => 'select',
									'label' => false,
									'value' => $child->amount_type,
									'list' => ['net' => $t('net'), 'gross' => $t('gross')]
								]) ?>
							<td class="money--f price-amount--f">
								<?= $this->form->field("positions.{$key}.amount", [
									'type' => 'text',
									'label' => false,
									'value' => $this->money->format($child->amount, ['currency' => false]),
									'class' => 'input--money'
								]) ?>
							<td class="money--f position-total--f">
								<?= $this->money->format($child->total()->getNet()) ?>
							<td class="actions">
								<?php if ($item->status === 'created'): ?>
									<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
								<?php endif ?>
					<?php endforeach ?>
					<?php if ($item->status === 'created'): ?>
					<tr class="nested-add nested-item">
						<td>
							<?= $this->form->field('positions.new.description', [
								'type' => 'text',
								'label' => false
							]) ?>
						<td>
							<?= $this->form->field('positions.new.quantity', [
								'type' => 'text',
								'value' => 1,
								'label' => false
							]) ?>
						<td>
							<?= $this->form->field("positions.new.amount_currency", [
								'type' => 'select',
								'label' => false,
								'list' => $currencies
							]) ?>
						<td>
							<?= $this->form->field("positions.new.amount_type", [
								'type' => 'select',
								'label' => false,
								'list' => ['net' => $t('net'), 'gross' => $t('gross')]
							]) ?>
						<td class="money--f price-amount--f">
							<?= $this->form->field('positions.new.amount', [
								'type' => 'text',
								'label' => false,
								'class' => 'input--money'
							]) ?>
						<td class="position-total--f">
						<td class="actions">
							<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
					<?php endif ?>
					</tbody>
					<tfoot>
						<?php if ($item->status === 'created'): ?>
						<tr>
							<td colspan="7" class="nested-add-action">
								<?= $this->form->button($t('add position'), ['type' => 'button', 'class' => 'button add-nested']) ?>
						<?php endif ?>
						<tr class="totals">
							<td colspan="5"><?= $t('Total value (net)') ?>
							<td><?= $this->price->format($item->totals(), 'net') ?>
						<tr>
					</tfoot>
				</table>
					<div class="help">
						<?= $t('To reference products in positions add the product number in braces to the description i.e. `(#123)`.') ?>
						<strong>
						<?php if ($item->status === 'shipped'): ?>
							<?= $t('Referenced products have their stock taken.') ?>
						<?php elseif($item->status === 'cancelled'): ?>
							<?= $t('Referenced products have their stock not taken or reserved.') ?>
						<?php elseif ($item->status): ?>
							<?= $t('Referenced products have their stock reserved.') ?>
						<?php endif ?>
						</strong>
					</div>
			</section>
		</div>

		<div class="bottom-actions">
			<div class="bottom-actions__left">
				<!-- cancel -->
			</div>
			<div class="bottom-actions__right">
				<?php if ($item->exists()): ?>
					<?= $this->html->link($t('PDF'), [
						'controller' => 'Shipments',
						'id' => $item->id, 'action' => 'export_pdf',
					], ['class' => 'button large']) ?>
				<?php endif ?>
				<?= $this->form->button($t('save'), [
					'type' => 'submit',
					'class' => 'button large save'
				]) ?>
			</div>
		</div>

	<?=$this->form->end() ?>
</article>