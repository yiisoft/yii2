<?php
/* @var $panel yii\debug\panels\ConfigPanel */
?>
<div class="yii-debug-toolbar-block">
    <a href="<?= $panel->getUrl() ?>">
        Yii
        <span class="label" title="Running on PHP <?= $panel->data['php']['version'] ?>"><?= $panel->data['application']['yii'] ?></span>
    </a>
</div>
