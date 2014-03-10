<?php

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

		<?= $this->form->field('billing_invoice_id', [
			'type' => 'select',
			'label' => $t('Invoice number'),
			'disabled' => $item->exists(),
			'list' => $invoices
		]) ?>

		<?= $this->form->field('ecommerce_shipment_id', [
			'type' => 'select',
			'label' => $t('Shipment'),
			'disabled' => $item->exists(),
			'list' => $shipments
		]) ?>

		<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large']) ?>

	<?=$this->form->end() ?>
</article>