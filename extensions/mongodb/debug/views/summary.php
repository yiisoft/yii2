<?php
/**
 * @var yii\mongodb\debug\MongoDbPanel $panel
 * @var integer $queryCount
 * @var integer $queryTime
 */
?>
<?php if ($queryCount): ?>
<div class="yii-debug-toolbar-block">
    <a href="<?= $panel->getUrl() ?>" title="Executed <?= $queryCount ?> MongoDB queries which took <?= $queryTime ?>.">
        MongoDB <span class="label label-info"><?= $queryCount ?></span> <span class="label"><?= $queryTime ?></span>
    </a>
</div>
<?php endif; ?>
