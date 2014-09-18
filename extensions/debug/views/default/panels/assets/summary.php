<?php
/* @var $panel yii\debug\panels\AssetPanel */
if (!empty($panel->data)):
?>
<div class="yii-debug-toolbar-block">
    <a href="<?= $panel->getUrl() ?>" title="Number of asset bundles loaded">Asset Bundles <span class="label label-info"><?= count($panel->data) ?></span></a>
</div>
<?php endif; ?>
