<?php

use lithium\core\Environment;

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
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<?=$this->form->create($item) ?>
		<?= $this->form->field('id', ['type' => 'hidden']) ?>

		<div class="grid-row">
			<div class="grid-column-left">
				<?= $this->form->field('number', [
					'label' => $t('Number'),
					'disabled' => true,
					'class' => 'use-for-title'
				]) ?>
				<?= $this->form->field('uuid', [
					'label' => $t('ID'),
					'disabled' => true
				]) ?>
			</div>
			<div class="grid-column-right">
				<?= $this->form->field('status', [
					'type' => 'select',
					'label' => $t('Status'),
					'list' => $statuses
				]) ?>
				<?= $this->form->field('created', [
					'label' => $t('Created'),
					'disabled' => true,
					'value' => $this->date->format($item->created, 'datetime')
				]) ?>
			</div>
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

		<?php $user = $item->user() ?>
		<div class="grid-row">
			<h1 class="h-gamma"><?= $t('User') ?></h1>
			<div class="grid-column-left">
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
			<div class="grid-column-right">
				<?= $this->form->field('user.number', [
					'label' => $t('Number'),
					'disabled' => true,
					'value' => $user->number
				]) ?>
				<?= $this->form->field('user.created', [
					'label' => $t('Signed up'),
					'disabled' => true,
					'value' => $this->date->format($user->created, 'datetime')
				]) ?>
			</div>
			<div class="actions">
				<?= $this->html->link($t('open user'), [
					'controller' => $user->isVirtual() ? 'VirtualUsers' : 'Users',
					'action' => 'edit',
					'id' => $user->id,
					'library' => 'cms_core'
				], ['class' => 'button']) ?>
			</div>
		</div>

		<div class="grid-row">
			<h1 class="h-gamma"><?= $t('Invoice') ?></h1>
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
						'value' => $this->money->format($item->totalAmount($item->user(), $item->cart(), $item->user()->taxZone())->getNet(), 'money')
					]) ?>
				</div>
				<div class="actions">
					<?= $this->html->link($t('PDF'), [
						'controller' => 'Invoices',
						'id' => $invoice->id, 'action' => 'export_pdf',
						'library' => 'cms_billing'
					], ['class' => 'button']) ?>
					<?= $this->html->link($t('XLSX'), [
						'controller' => 'Invoices',
						'id' => $invoice->id, 'action' => 'export_excel',
						'library' => 'cms_billing'
					], ['class' => 'button']) ?>
					<?= $this->html->link($t('open invoice'), [
						'controller' => 'Invoices',
						'action' => 'edit',
						'id' => $invoice->id,
						'library' => 'cms_billing'
					], ['class' => 'button']) ?>
				</div>
			<?php endif ?>
		</div>

		<div class="grid-row grid-row-last">
			<h1 class="h-gamma"><?= $t('Shipment') ?></h1>
			<?php if ($shipment = $item->shipment()): ?>
				<div class="grid-column-left">
					<?= $this->form->field('shipping_address', [
						'type' => 'textarea',
						'label' => $t('Address'),
						'disabled' => true,
						'value' => $shipment->address()->format('postal', $locale)
					]) ?>
					<?= $this->form->field('shipping.address_phone', [
						'label' => $t('Phone'),
						'disabled' => true,
						'value' => $shipment->address()->phone
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
					<?= $this->form->field('shipment.tracking', [
						'label' => $t('Tracking Number'),
						'disabled' => true,
						'value' => $shipment->tracking
					]) ?>
					<div class="help"><?= $t('Tracking is available once status is `shipped`.') ?></div>
				</div>

				<div class="actions">
					<?= $this->html->link($t('open shipment'), [
						'controller' => 'Shipments',
						'action' => 'edit',
						'id' => $shipment->id,
						'library' => 'ecommerce_core'
					], ['class' => 'button']) ?>
				</div>
			<?php endif ?>
		</div>

		<div class="bottom-actions">
			<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large save']) ?>
		</div>
	<?=$this->form->end() ?>
</article>