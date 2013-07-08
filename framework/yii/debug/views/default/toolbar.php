<?php
/**
 * @var \yii\base\View $this
 * @var \yii\debug\Panel[] $panels
 * @var string $tag
 */
use yii\helpers\Html;
?>
<style>
	<?php echo $this->renderFile(__DIR__ . '/toolbar.css'); ?>
</style>
<div id="yii-debug-toolbar">
	<?php foreach ($panels as $panel): ?>
	<?php echo $panel->getSummary(); ?>
	<?php endforeach; ?>
</div>
