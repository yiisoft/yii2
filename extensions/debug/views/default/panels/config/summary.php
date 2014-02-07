<?php

use yii\helpers\Html;

/**
 * @var yii\debug\panels\ConfigPanel $panel
 */
?>
<div class="yii-debug-toolbar-block">
	<a href="<?= $panel->getUrl() ?>">
		Yii
		<span class="label label-info"><?= $panel->data['application']['yii'] ?></span>
		PHP
		<span class="label label-info"><?= $panel->data['php']['version'] ?></span>
	</a>
</div>
