<?php

use SebastianBergmann\Money\IntlFormatter;

$dateFormatter = new IntlDateFormatter(
	$locale,
	IntlDateFormatter::SHORT,
	IntlDateFormatter::SHORT,
	$authedUser['timezone']
);

$moneyFormatter = new IntlFormatter($locale);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<h1 class="alpha"><?= $this->title($t('Carts')) ?></h1>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td><?= $t('User session ID') ?>
					<td><?= $t('Total net amount') ?>
					<td><?= $t('Total quantity') ?>
					<td class="date created"><?= $t('Created') ?>
					<td class="date created"><?= $t('Modified') ?>
					<td>
			</thead>
			<tbody>
				<?php foreach ($data as $item): ?>
				<tr data-id="<?= $item->id ?>">
					<td><?= $item->user_session_id ?>
					<td><?= $moneyFormatter->format($item->totalAmount('net', $taxZone, 'EUR')) ?>
					<td><?= $item->totalQuantity() ?>
					<td class="date created">
						<?php $date = DateTime::createFromFormat('Y-m-d H:i:s', $item->created) ?>
						<time datetime="<?= $date->format(DateTime::W3C) ?>"><?= $dateFormatter->format($date) ?></time>
					<td class="date modified">
						<?php $date = DateTime::createFromFormat('Y-m-d H:i:s', $item->modified) ?>
						<time datetime="<?= $date->format(DateTime::W3C) ?>"><?= $dateFormatter->format($date) ?></time>
					<td>
						<nav class="actions">
							<?= $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'cms_ecommerce'], ['class' => 'button']) ?>
							<?= $this->html->link($t('edit'), ['id' => $item->id, 'action' => 'edit', 'library' => 'cms_ecommerce'], ['class' => 'button']) ?>
						</nav>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>
</article>