<?php
/**
 * @var yii\debug\panels\DbPanel $panel
 * @var integer $queryCount
 * @var integer $queryTime
 */
?>
<?php if ($queryCount): ?>
<div class="yii-debug-toolbar-block">
    <a href="<?= $panel->getUrl() ?>" title="Executed <?= $queryCount ?> database queries which took <?= $queryTime ?>.">
        DB <span class="label label-info"><?= $queryCount ?></span> <span class="label"><?= $queryTime ?></span>
    </a>
</div>
<?php endif; ?>
