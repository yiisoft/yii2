<?php

use yii\helpers\Html;

/**
 * @var yii\debug\panels\ConfigPanel $panel
 */
?>
<div class="yii-debug-toolbar-block">
	<a href="<?= $panel->getUrl() ?>">
		<img width="29" height="30" alt="" src="<?= $panel->getYiiLogo() ?>">
		<span><?= $panel->data['application']['yii'] ?></span>
	</a>
</div>
<div class="yii-debug-toolbar-block">
	<?= Html::a('PHP ' . $panel->data['php']['version'], ['phpinfo'], ['title' => 'Show phpinfo()', 'target' => 'phpinfo']) ?>
</div>
