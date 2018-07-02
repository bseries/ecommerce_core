<?php

use base_core\extensions\cms\Settings;
use lithium\core\Environment;
use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'ecommerce_core', 'default' => $message]);
};

$locale = Environment::get('locale');

$this->set([
	'page' => [
		'type' => 'single',
		'title' => $item->number,
		'empty' => false,
		'object' => $t('order')
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

		<?php if ($useSites): ?>
			<div class="grid-row">
				<h1><?= $t('Sites') ?></h1>

				<div class="grid-column-left"></div>
				<div class="grid-column-right">
					<?= $this->form->field('site', [
						'type' => 'select',
						'label' => $t('Site'),
						'list' => $sites
					]) ?>
				</div>
			</div>
		<?php endif ?>

		<div class="grid-row">
			<div class="grid-column-left">
				<?= $this->form->field('number', [
					'type' => 'text',
					'label' => $t('Number'),
					'class' => 'use-for-title',
					'placeholder' => $autoNumber ? $t('Will autogenerate number.') : null,
					'disabled' => $autoNumber && !$item->exists(),
					'readonly' => $autoNumber || $item->exists()
				]) ?>
				<div class="help">
					<?= $t('The reference number uniquely identifies this item and is used especially in correspondence with clients and customers.') ?>
				</div>

				<?= $this->form->field('uuid', [
					'label' => $t('UUID'),
					'disabled' => true
				]) ?>
			</div>
			<div class="grid-column-right">
				<?= $this->form->field('status', [
					'type' => 'select',
					'label' => $t('Status'),
					'list' => $statuses
				]) ?>
				<div class="help">
					<?= $t('Cancelling an order will also cancel associated invoice and shipment - if possible.') ?>
					<?php if (Settings::read('shipment.sendCheckedOutMail')): ?>
						<strong>
							<?= $t('The user will be notified by e-mail when the status is changed to `checked-out`.') ?>
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
					'disabled' => true
				]) ?>

			</div>
			<?php if ($user = $item->user()): ?>
			<div class="grid-column-right">
				<?= $this->form->field('user.number', [
					'label' => $t('Number'),
					'readonly' => true,
					'value' => $user->number
				]) ?>
				<?= $this->form->field('user.name', [
					'label' => $t('Name'),
					'readonly' => true,
					'value' => $user->name
				]) ?>
				<?= $this->form->field('user.email', [
					'label' => $t('Email'),
					'readonly' => true,
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
			<div class="grid-column-left">
				<?= $this->form->field('user_note', [
					'type' => 'textarea',
					'label' => $t('Note from user'),
					'disabled' => true
				]) ?>
			</div>
			<div class="grid-column-right">
				<?= $this->form->field('internal_note', [
					'type' => 'textarea',
					'label' => $t('Private note'),
				]) ?>
				<div class="help"><?= $t('Never visible to user.') ?></div>
			</div>
		</div>

		<div class="grid-row">
			<h1 class="h-gamma"><?= $t('Payment') ?></h1>
			<?php if ($invoice = $item->invoice()): ?>
				<div class="grid-column-left">
					<?= $this->form->field('billing_address', [
						'type' => 'textarea',
						'label' => $t('Address'),
						'disabled' => true,
						'value' => $invoice->address()->format('postal', $locale)
					]) ?>
					<?= $this->form->field('billing.address_phone', [
						'label' => $t('Phone'),
						'disabled' => true,
						'value' => $invoice->address()->phone
					]) ?>
				</div>
				<div class="grid-column-right">
					<?= $this->form->field('invoice.status', [
						'type' => 'select',
						'label' => $t('Status'),
						'list' => $invoiceStatuses,
						'disabled' => true,
						'value' => $invoice->status
					]) ?>

					<?= $this->form->field('order.payment_method', [
						'label' => $t('Payment method'),
						'value' => $item->payment_method
					]) ?>

					<?= $this->form->field('invoice.number', [
						'label' => $t('Number'),
						'disabled' => true,
						'value' => $invoice->number
					]) ?>
					<?= $this->form->field('invoice.created', [
						'label' => $t('Created'),
						'disabled' => true,
						'value' => $this->date->format($invoice->created, 'datetime')
					]) ?>
					<?= $this->form->field('total_net', [
						'label' => $t('Total (net)'),
						'disabled' => true,
						'value' => $this->price->format($item->totals($item->user()), 'net', ['currency' => false])
					]) ?>
				</div>
				<div class="actions">
					<?= $this->html->link($t('PDF'), [
						'controller' => 'Invoices',
						'id' => $item->id, 'action' => 'export_pdf'
					], ['class' => 'button', 'download' => "invoice_{$invoice->number}.pdf"]) ?>

					<?= $this->html->link($t('open invoice'), [
						'controller' => 'Invoices',
						'action' => 'edit',
						'id' => $invoice->id,
						'library' => 'billing_invoice'
					], ['class' => 'button']) ?>
				</div>
			<?php else: ?>
				<div class="none-available"><?= $t('There is no invoice attached to this order.') ?></div>
			<?php endif ?>
		</div>

		<div class="grid-row">
			<h1 class="h-gamma"><?= $t('Shipment') ?></h1>
			<?php if ($shipment = $item->shipment()): ?>
				<div class="grid-column-left">
					<?= $this->form->field('shipping_address', [
						'type' => 'textarea',
						'label' => $t('Address'),
						'disabled' => true,
						'value' => $shipment->address()->format('postal', $locale)
					]) ?>
				</div>
				<div class="grid-column-right">
					<?= $this->form->field('shipment.status', [
						'type' => 'select',
						'label' => $t('Status'),
						'list' => $shipmentStatuses,
						'disabled' => true,
						'value' => $shipment->status
					]) ?>

					<?= $this->form->field('order.shipping_method', [
						'label' => $t('Shipping method'),
						'value' => $item->shipping_method
					]) ?>

					<?= $this->form->field('shipment.number', [
						'label' => $t('Number'),
						'disabled' => true,
						'value' => $shipment->number
					]) ?>
					<?= $this->form->field('shipment.created', [
						'label' => $t('Created'),
						'disabled' => true,
						'value' => $this->date->format($shipment->created, 'datetime')
					]) ?>
				</div>

				<div class="actions">
					<?= $this->html->link($t('PDF'), [
						'controller' => 'Shipments',
						'id' => $item->id, 'action' => 'export_pdf'
					], ['class' => 'button', 'download' => "shipment_{$shipment->number}.pdf"]) ?>

					<?= $this->html->link($t('open shipment'), [
						'controller' => 'Shipments',
						'action' => 'edit',
						'id' => $shipment->id,
						'library' => 'ecommerce_core'
					], ['class' => 'button']) ?>
				</div>
			<?php else: ?>
				<div class="none-available"><?= $t('There is no shipment attached to this order.') ?></div>
			<?php endif ?>
		</div>

		<div class="bottom-actions">
			<div class="bottom-actions__left">
				<!-- cancel -->
			</div>
			<div class="bottom-actions__right">
				<?= $this->form->button($t('save'), [
					'type' => 'submit',
					'class' => 'button large save'
				]) ?>
			</div>
		</div>

	<?=$this->form->end() ?>
</article>
