<?php

$dateFormatter = new IntlDateFormatter(
	'de_DE',
	IntlDateFormatter::SHORT,
	IntlDateFormatter::SHORT,
	$authedUser['timezone']
);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<h1 class="alpha"><?= $this->title($t('Orders')) ?></h1>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td class="emphasize"><?= $t('Number') ?>
					<td><?= $t('Status') ?>
					<td><?= $t('User') ?>
					<td><?= $t('Invoice number') ?>
					<td class="status"><?= $t('Invoice status') ?>
					<td class="status"><?= $t('Shipment status') ?>
					<td class="date created"><?= $t('Created') ?>
					<td>
			</thead>
			<tbody>
				<?php foreach ($data as $item): ?>
					<?php $user = $item->user() ?>
				<tr data-id="<?= $item->id ?>">
					<td class="emphasize"><?= $item->number ?: '–' ?>
					<td class="status"><?= $item->status ?>
					<?php if ($user->isVirtual()): ?>
						<td>
							<?= $this->html->link($user->name . '/' . $user->id, [
								'controller' => 'VirtualUsers', 'action' => 'edit', 'id' => $user->id, 'library' => 'cms_core'
							]) ?>
							(<?= $this->html->link('virtual', [
								'controller' => 'VirtualUsers', 'action' => 'index', 'library' => 'cms_core'
							]) ?>)
					<?php else: ?>
						<td>
							<?= $this->html->link($user->name . '/' . $user->id, [
								'controller' => 'Users', 'action' => 'edit', 'id' => $user->id, 'library' => 'cms_core'
							]) ?>
							(<?= $this->html->link('real', [
								'controller' => 'Users', 'action' => 'index', 'library' => 'cms_core'
							]) ?>)
					<?php endif ?>
					<td>
					<?php
					if ($sub = $item->invoice()) {
						echo $this->html->link($sub->number, ['controller' => 'invoices', 'library' => 'cms_billing', 'id' => $sub->id, 'action' => 'edit']);
					} else {
						echo '–';
					}
					?>
					<td class="status"><?= ($sub = $item->invoice()) ? $sub->status : '–' ?>
					<td class="status"><?= ($sub = $item->shipment()) ? $sub->status : '–' ?>
					<td class="date created">
						<?php $date = DateTime::createFromFormat('Y-m-d H:i:s', $item->created) ?>
						<time datetime="<?= $date->format(DateTime::W3C) ?>"><?= $dateFormatter->format($date) ?></time>
					<td>
						<nav class="actions">
							<?= $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'cms_ecommerce'], ['class' => 'button']) ?>
							<?= $this->html->link($t('paid'), ['id' => $item->id, 'action' => 'paid', 'library' => 'cms_ecommerce'], ['class' => 'button']) ?>
							<?= $this->html->link($t('ship'), ['id' => $item->id, 'action' => 'ship', 'library' => 'cms_ecommerce'], ['class' => 'button']) ?>
							<?= $this->html->link($t('edit'), ['id' => $item->id, 'action' => 'edit', 'library' => 'cms_ecommerce'], ['class' => 'button']) ?>
						</nav>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>
</article>