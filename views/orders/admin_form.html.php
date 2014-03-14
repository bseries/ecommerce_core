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
		<span class="title" data-untitled="<?= $untitled ?>"><?= $title['title'] ?></span>
	</h1>

	<?=$this->form->create($item) ?>
		<?= $this->form->field('number', [
			'type' => 'text',
			'label' => $t('Number'),
			'disabled' => true,
			'class' => 'use-for-title'
		]) ?>
		<div class="help"><?= $t('The order number is automatically generated.') ?></div>

		<section>
			<h1 class="gamma"><?= $t('Billing') ?></h1>
			<?= $this->form->field('billing_invoice_id', [
				'type' => 'select',
				'label' => $t('Invoice'),
				'disabled' => $item->exists(),
				'list' => $invoices
			]) ?>
			<?= $this->form->field('billing_address', [
				'type' => 'textarea',
				'label' => $t('Billing address'),
				'disabled' => true,
				'value' => $item->address('billing')->format('postal', $locale)
			]) ?>
		</section>
		<section>
			<h1 class="gamma"><?= $t('Shipment') ?></h1>
			<?= $this->form->field('ecommerce_shipment_id', [
				'type' => 'select',
				'label' => $t('Shipment'),
				'disabled' => $item->exists(),
				'list' => $shipments
			]) ?>
			<?= $this->form->field('shipping_address', [
				'type' => 'textarea',
				'label' => $t('Shipping address'),
				'disabled' => true,
				'value' => $item->address('shipping')->format('postal', $locale)
			]) ?>
		</section>

		<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large']) ?>

	<?=$this->form->end() ?>
</article>