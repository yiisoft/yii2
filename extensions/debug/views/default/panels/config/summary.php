<?php
/* @var $panel yii\debug\panels\ConfigPanel */
?>
<div class="yii-debug-toolbar-block">
    <a href="<?= $panel->getUrl() ?>">
        Yii
        <span class="label"><?= $panel->data['application']['yii'] ?></span>
        PHP
        <span class="label"><?= $panel->data['php']['version'] ?></span>
    </a>
</div>
