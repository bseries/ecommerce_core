<?php

use base_core\extensions\cms\Features;

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
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<?=$this->form->create($item) ?>
		<?= $this->form->field('id', [
			'type' => 'hidden'
		]) ?>

		<div class="grid-row">
			<div class="grid-column-left">
				<?= $this->form->field('number', [
					'type' => 'text',
					'label' => $t('Number'),
					'class' => 'use-for-title'
				]) ?>
				<div class="help"><?= $t('Leave empty to autogenerate number.') ?></div>
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
			<div class="grid-column-right">
				<?= $this->form->field('status', [
					'type' => 'select',
					'label' => $t('Status'),
					'list' => $statuses
				]) ?>
				<?php if (Features::enabled('shipment.sendShippedMail')): ?>
				<div class="help">
					<?= $t('The user will be notified by e-mail when the status is changed to `shipped`.') ?>
				</div>
				<?php endif ?>

				<?= $this->form->field('created', [
					'label' => $t('Created'),
					'disabled' => true,
					'value' => $this->date->format($item->created, 'datetime')
				]) ?>
			</div>
		</div>

		<div class="grid-row">
			<div class="grid-column-left">
				<?= $this->form->field('address', [
					'type' => 'textarea',
					'label' => $t('Address'),
					'disabled' => true,
					'value' => $item->address()->format('postal', $locale)
				]) ?>
			</div>
			<div class="grid-column-right">
				<div class="compound-users">
					<?php
						$user = $item->exists() ? $item->user() : false;
					?>
					<?= $this->form->field('user_id', [
						'type' => 'select',
						'label' => $t('User'),
						'list' => $users,
						'class' => !$user || !$user->isVirtual() ? null : 'hide'
					]) ?>
					<?= $this->form->field('virtual_user_id', [
						'type' => 'select',
						'label' => false,
						'list' => $virtualUsers,
						'class' => $user && $user->isVirtual() ? null : 'hide'
					]) ?>
					<?= $this->form->field('user.is_real', [
						'type' => 'checkbox',
						'label' => $t('real user'),
						'checked' => $user ? !$user->isVirtual() : true
					]) ?>
				</div>
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
			<section class="use-nested">
				<h1 class="h-gamma"><?= $t('Positions') ?></h1>
				<table>
					<thead>
						<tr>
							<td><?= $t('Description') ?>
							<td><?= $t('Quantity') ?>
							<td><?= $t('Currency') ?>
							<td><?= $t('Type') ?>
							<td><?= $t('Unit value') ?>
							<td><?= $t('Line total value (net)') ?>
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
									'disabled' => true,
									'list' => ['net' => $t('net'), 'gross' => $t('gross')]
								]) ?>
							<td>
								<?= $this->form->field("positions.{$key}.amount", [
									'type' => 'text',
									'label' => false,
									'value' => $this->money->format($child->amount(), 'decimal')
								]) ?>
							<td>
								<?= $this->form->field("positions.{$key}.total_net", [
									'type' => 'text',
									'label' => false,
									'disabled' => true,
									'value' => $this->money->format($child->totalAmount()->getNet(), 'decimal')
								]) ?>
							<td class="actions">
							<?php if (!$item->is_locked): ?>
								<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
							<?php endif ?>
					<?php endforeach ?>
					<?php if (!$item->is_locked): ?>
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
									'disabled' => true,
									'list' => ['net' => $t('net'), 'gross' => $t('gross')]
								]) ?>
							<td>
								<?= $this->form->field('positions.new.amount', [
									'type' => 'text',
									'label' => false
								]) ?>
							<td>
							<td class="actions">
								<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
					<?php endif ?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="7" class="nested-add-action">
								<?= $this->form->button($t('add position'), ['type' => 'button', 'class' => 'button add-nested']) ?>
						<tr>
							<td colspan="6"><?= $t('Total value (net)') ?>
							<td><?= ($money = $item->totalAmount()) ? $this->money->format($money->getNet(), 'money') : null ?>
						<tr>
					</tfoot>
				</table>
			</section>
		</div>

		<div class="bottom-actions">
			<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large save']) ?>
		</div>
	<?=$this->form->end() ?>
</article>