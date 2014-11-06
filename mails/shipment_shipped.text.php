Hello <?= $user->name ?>,

Your order #<?= $order->number ?> from <?= $this->date->format($item->created, 'date') ?> has
just been shipped.

Positions

<?php foreach ($order->positions() as $position): ?>
- <?= $position->description ?>

<?php endforeach ?>
