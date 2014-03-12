<?php

use lithium\core\Environment;

$dateFormatter = new IntlDateFormatter(
	Environment::get('locale'),
	IntlDateFormatter::SHORT,
	IntlDateFormatter::SHORT,
	$authedUser['timezone']
);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<h1 class="alpha"><?= $this->title($t('Products')) ?></h1>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td>
					<td class="flag"><?= $t('publ.?') ?>
					<td>
					<td class="emphasize"><?= $t('Title') ?>
					<td><?= $t('Number') ?>
					<td class="date created"><?= $t('Created') ?>
					<td>
			</thead>
			<tbody>
				<?php foreach ($data as $item): ?>
				<tr data-id="<?= $item->id ?>">
					<td>
					<td class="flag"><?= ($item->is_published ? '✓' : '╳') ?>
					<td>
						<?php if ($cover = $item->cover()): ?>
							<?= $this->media->image($cover->version('fix3')->url('http'), ['class' => 'media']) ?>
						<?php endif ?>
					<td class="emphasize"><?= $item->title ?>
					<td>
					<td class="date created">
						<?php $date = DateTime::createFromFormat('Y-m-d H:i:s', $item->created) ?>
						<time datetime="<?= $date->format(DateTime::W3C) ?>"><?= $dateFormatter->format($date) ?></time>
					<td>
						<nav class="actions">
							<?= $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'cms_ecommerce'], ['class' => 'button']) ?>
							<?= $this->html->link($item->is_published ? $t('unpublish') : $t('publish'), ['id' => $item->id, 'action' => $item->is_published ? 'unpublish': 'publish', 'library' => 'cms_ecommerce'], ['class' => 'button']) ?>
							<?= $this->html->link($t('edit'), ['id' => $item->id, 'action' => 'edit', 'library' => 'cms_ecommerce'], ['class' => 'button']) ?>
						</nav>
					<?php foreach ($item->products() as $sub): ?>
						<tr class="sub-item">
							<td>↳
							<td class="flag"><?= ($sub->is_published ? '✓' : '╳') ?>
							<td>
								<?php if ($cover = $sub->cover()): ?>
									<?= $this->media->image($cover->version('fix3')->url('http'), ['class' => 'media']) ?>
								<?php endif ?>
							<td class="emphasize"><?= $sub->title ?>
							<td class="emphasize">#<?= $sub->number ?>
							<td class="date created">
								<?php $date = DateTime::createFromFormat('Y-m-d H:i:s', $sub->created) ?>
								<time datetime="<?= $date->format(DateTime::W3C) ?>"><?= $dateFormatter->format($date) ?></time>
							<td>
								<nav class="actions">
									<?= $this->html->link($t('delete'), ['id' => $sub->id, 'controller' => 'Products', 'action' => 'delete', 'library' => 'cms_ecommerce'], ['class' => 'button']) ?>
									<?= $this->html->link($sub->is_published ? $t('unpublish') : $t('publish'), ['id' => $sub->id, 'controller' => 'Products', 'action' => $sub->is_published ? 'unpublish': 'publish', 'library' => 'cms_ecommerce'], ['class' => 'button']) ?>
									<?= $this->html->link($t('edit'), ['id' => $sub->id, 'controller' => 'Products', 'action' => 'edit', 'library' => 'cms_ecommerce'], ['class' => 'button']) ?>
								</nav>

					<?php endforeach ?>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>
</article>