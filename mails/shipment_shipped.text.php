Hello <?= $user->name ?>,

Your order #<?= $order->number ?> from <?= $this->date->format($order->created, 'date') ?> has
just been shipped.

Positions

<?php foreach ($shipment->positions() as $position): ?>
- <?= $position->description ?>

<?php endforeach ?>