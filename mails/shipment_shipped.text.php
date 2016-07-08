Hello <?= $user->name ?>,

<?php if ($order): ?>
Your order #<?= $order->number ?> from <?= $this->date->format($order->created, 'date') ?> has
just been shipped.
<?php else: ?>
Your shipment #<?= $shipment->number ?> from <?= $this->date->format($shipment->created, 'date') ?> has
just been shipped.
<?php endif ?>

Positions

<?php foreach ($shipment->positions() as $position): ?>
- <?= $position->description ?>

<?php endforeach ?>