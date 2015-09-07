<?php

use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'ecommerce_core', 'default' => $message]);
};

$untitled = $t('Untitled');

$title = [
	'action' => ucfirst($this->_request->action === 'add' ? $t('creating') : $t('editing')),
	'title' => $item->number ?: $untitled,
	'object' => [ucfirst($t('order')), ucfirst($t('orders'))]
];
$this->title("{$title['title']} - {$title['object'][1]}");

?>
<article>
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

		<div class="bottom-actions">
			<div class="bottom-actions__left">
				<?php if ($item->exists()): ?>
					<?= $this->html->link($t('delete'), [
						'action' => 'delete', 'id' => $item->id
					], ['class' => 'button large delete']) ?>
				<?php endif ?>
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