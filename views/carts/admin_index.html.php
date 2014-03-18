<?php

use SebastianBergmann\Money\IntlFormatter;

$dateFormatter = new IntlDateFormatter(
	$locale,
	IntlDateFormatter::SHORT,
	IntlDateFormatter::SHORT,
	$authedUser['timezone']
);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<h1 class="alpha"><?= $this->title($t('Carts')) ?></h1>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td><?= $t('User Session ID') ?>
					<td><?= $t('User ID') ?>
					<td><?= $t('Total amount (net) ') ?>
					<td><?= $t('Total quantity') ?>
					<td class="date created"><?= $t('Created') ?>
					<td class="date created"><?= $t('Modified') ?>
					<td>
			</thead>
			<tbody>
				<?php foreach ($data as $item): ?>
					<?php $user = $item->order()->user() ?>
					<?php $taxZone = $user->taxZone() ?>
				<tr data-id="<?= $item->id ?>">
					<td><?= $item->user_session_id ?>
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
					<td><?= $this->money->format($item->totalAmount($user, 'net', $taxZone, 'EUR'), 'money') ?>
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
							<? // $this->html->link($t('edit'), ['id' => $item->id, 'action' => 'edit', 'library' => 'cms_ecommerce'], ['class' => 'button']) ?>
						</nav>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>
</article>