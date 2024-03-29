Hello <?= $user->name ?>,

thank you for your order #<?= $order->number ?>. We’re going to process your
order as soon as possible.

<?php if ($invoice): ?>
	Attached to this mail is the invoice.

	<?= wordwrap($order->paymentMethod()->info('checkout.success', 'text', $this, $order)) ?>
<?php endif ?>
