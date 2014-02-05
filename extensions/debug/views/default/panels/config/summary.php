<?php

use yii\helpers\Html;

/**
 * @var yii\debug\panels\ConfigPanel $panel
 */
?>
<div class="yii-debug-toolbar-block">
	<a href="<?= $panel->getUrl() ?>">
		<img width="29" height="30" alt="" src="<?= $panel->getYiiLogo() ?>">
		<span><span class="label"><?= $panel->data['application']['yii'] ?></span> PHP <span class="label"><?= $panel->data['php']['version'] ?></span></span>
	</a>
</div>