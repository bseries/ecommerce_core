<?php

use lithium\core\Environment;

$locale = Environment::get('locale');

$untitled = $t('Untitled');

$title = [
	'action' => ucfirst($this->_request->action === 'add' ? $t('creating') : $t('editing')),
	'title' => $item->number ?: $untitled,
	'object' => [ucfirst($t('order')), ucfirst($t('orders'))]
];
$this->title("{$title['title']} - {$title['object'][1]}");

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?> section-spacing">
	<h1 class="alpha">
		<span class="action"><?= $title['action'] ?></span>
		<span class="object"><?= $title['object'][0] ?></span>
		<span class="title" data-untitled="<?= $untitled ?>"><?= $title['title'] ?></span>
	</h1>

	<nav class="actions">

	</nav>

	<?=$this->form->create($item) ?>
		<section>
			<h1 class="beta"><?= $t('General') ?></h1>
			<?= $this->form->field('status', [
				'type' => 'select',
				'label' => $t('Status'),
				'list' => $statuses
			]) ?>


			<?= $this->form->field('number', [
				'label' => $t('Number'),
				'disabled' => true,
				'class' => 'use-for-title'
			]) ?>
			<?= $this->form->field('uuid', [
				'label' => $t('ID'),
				'disabled' => true
			]) ?>
			<?= $this->form->field('total', [
				'label' => $t('Total (net/gross)'),
				'disabled' => true,
				'value' => $this->money->format($item->totalAmount($item->user(), $item->cart(), $item->user()->taxZone())->getNet(), 'money')
			]) ?>
			<?= $this->form->field('total_gross', [
				'label' => $t('Total (gross)'),
				'disabled' => true,
				'value' => $this->money->format($item->totalAmount($item->user(), $item->cart(), $item->user()->taxZone())->getGross(), 'money')
			]) ?>
			<?= $this->form->field('created', [
				'label' => $t('Created'),
				'disabled' => true,
				'value' => $this->date->format($item->created, 'datetime')
			]) ?>
			<?= $this->form->field('user_note', [
				'type' => 'textarea',
				'label' => $t('Note from user'),
				'disabled' => true
			]) ?>
			<?= $this->form->field('internal_note', [
				'type' => 'textarea',
				'label' => $t('Private note'),
			]) ?>
			<div class="help"><?= $t('Never visible to user.') ?>
		</section>
		<section>
			<h1 class="beta"><?= $t('User') ?></h1>
			<?php $user = $item->user() ?>
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
			<?= $this->form->field('user.created', [
				'label' => $t('Signed up'),
				'disabled' => true,
				'value' => $this->date->format($user->created, 'datetime')
			]) ?>
			<?= $this->html->link($t('edit user'), [
				'controller' => $user->isVirtual() ? 'VirtualUsers' : 'Users',
				'action' => 'edit',
				'id' => $user->id,
				'library' => 'cms_core'
			], ['class' => 'button']) ?>
		</section>
		<section>
			<h1 class="beta"><?= $t('Invoice') ?></h1>
			<?php $invoice = $item->invoice() ?>
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

			<?= $this->form->field('billing_address', [
				'type' => 'textarea',
				'label' => $t('Address'),
				'disabled' => true,
				'value' => $item->address('billing')->format('postal', $locale)
			]) ?>
			<?= $this->form->field('billing.address_phone', [
				'label' => $t('Phone'),
				'disabled' => true,
				'value' => $item->address('billing')->phone
			]) ?>

			<?= $this->html->link($t('edit invoice'), [
				'controller' => 'Invoices',
				'action' => 'edit',
				'id' => $item->billing_invoice_id,
				'library' => 'cms_billing'
			], ['class' => 'button']) ?>
			<?= $this->html->link($t('edit invoice address'), [
				'controller' => 'Addresses',
				'action' => 'edit',
				'id' => $item->address('billing')->id,
				'library' => 'cms_core'
			], ['class' => 'button']) ?>
			<section>
				<h1 class="gamma"><?= $t('Positions') ?></h1>

			</section>
		</section>
		<section>
			<h1 class="beta"><?= $t('Shipment') ?></h1>
			<?php $shipment = $item->shipment() ?>
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
			<?= $this->form->field('shipping_address', [
				'type' => 'textarea',
				'label' => $t('Address'),
				'disabled' => true,
				'value' => $item->address('shipping')->format('postal', $locale)
			]) ?>
			<?= $this->form->field('shipping.address_phone', [
				'label' => $t('Phone'),
				'disabled' => true,
				'value' => $item->address('shipping')->phone
			]) ?>
			<?= $this->html->link($t('edit shipment'), [
				'controller' => 'Shipments',
				'action' => 'edit',
				'id' => $item->ecommerce_shipment_id,
				'library' => 'ecommerce_core'
			], ['class' => 'button']) ?>
			<?= $this->html->link($t('edit shipping address'), [
				'controller' => 'Addresses',
				'action' => 'edit',
				'id' => $item->address('shipping')->id,
				'library' => 'cms_core'
			], ['class' => 'button']) ?>
		</section>

		<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large']) ?>

	<?=$this->form->end() ?>
</article>