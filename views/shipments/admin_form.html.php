<?php

use cms_core\extensions\cms\Features;

$this->set([
	'page' => [
		'type' => 'single',
		'title' => $item->number,
		'empty' => false,
		'object' => $t('shipment')
	],
	'meta' => [
		'status' => $statuses[$item->status]
	]
]);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<?=$this->form->create($item) ?>
		<?= $this->form->field('id', [
			'type' => 'hidden'
		]) ?>

		<div class="grid-row grid-row-last">
			<div class="grid-column-left">
				<?= $this->form->field('address', [
					'type' => 'textarea',
					'label' => $t('Address'),
					'disabled' => true,
					'value' => $item->address()->format('postal', $locale)
				]) ?>
			</div>
			<div class="grid-column-right">
				<?= $this->form->field('status', [
					'type' => 'select',
					'label' => $t('Status'),
					'list' => $statuses
				]) ?>
				<div class="help">
				<?php if (Features::enabled('shipment.sendShippedMail')): ?>
					<?= $t('The user will be notified by e-mail when the status is changed to `shipped`.') ?></div>
				<?php endif ?>
				<?= $this->form->field('method', [
					'type' => 'select',
					'label' => $t('Method'),
					'list' => $methods
				]) ?>
				<?= $this->form->field('created', [
					'label' => $t('Created'),
					'disabled' => true,
					'value' => $this->date->format($item->created, 'datetime')
				]) ?>
				<?= $this->form->field('tracking', [
					'label' => $t('Tracking Number'),
				]) ?>
				<div class="help"><?= $t('Tracking is available once status is `shipped`.') ?></div>
			</div>
		</div>

		<div class="bottom-actions">
			<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large save']) ?>
		</div>
	<?=$this->form->end() ?>
</article>