<?php

use yii\apidoc\templates\bootstrap\SideNavWidget;

/**
 * @var yii\web\View $this
 * @var string $content
 */

$this->beginContent('@yii/apidoc/templates/bootstrap/layouts/main.php'); ?>

<div class="row">
	<div class="col-md-2">
		<?php
		asort($headlines);
		$nav = [];
		$nav[] = [
			'label' => 'Index',
			'url' => $this->context->generateGuideUrl('index.md'),
			'active' => isset($currentFile) && (basename($currentFile) == 'index.md'),
		];
		foreach($headlines as $file => $headline) {
			$nav[] = [
				'label' => $headline,
				'url' => $this->context->generateGuideUrl($file),
				'active' => isset($currentFile) && ($file == $currentFile),
			];
		} ?>
		<?= SideNavWidget::widget([
			'id' => 'navigation',
			'items' => $nav,
			'view' => $this,
		]) ?>
	</div>
	<div class="col-md-9" role="main">
		<?= $content ?>
	</div>
</div>

<?php $this->endContent(); ?>