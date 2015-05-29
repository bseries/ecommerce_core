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
				<?php if (Settings::read('shipment.sendShippedMail')): ?>
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
							<td class="actions">
								<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
					<?php endforeach ?>
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
						<td class="actions">
							<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="7" class="nested-add-action">
								<?= $this->form->button($t('add position'), ['type' => 'button', 'class' => 'button add-nested']) ?>
						<tr>
					</tfoot>
				</table>
			</section>
		</div>

		<div class="bottom-actions">
			<?php if ($item->exists()): ?>

				<?= $this->html->link($t('PDF'), [
					'controller' => 'Shipments',
					'id' => $item->id, 'action' => 'export_pdf',
				], ['class' => 'button large']) ?>

			<?php endif ?>
			<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large save']) ?>
		</div>
	<?=$this->form->end() ?>
</article>