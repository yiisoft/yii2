<?php
/**
 * @var \yii\base\View $this
 * @var \yii\debug\Panel[] $panels
 */
?>
<style>
#yii-debug-toolbar {
	position: fixed;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #eee;
	border-top: 1px solid #ccc;
	margin: 0;
	padding: 5px 10px;
	z-index: 1000000;
	font: 11px Verdana, Arial, sans-serif;
	text-align: left;
}
.yii-debug-toolbar-block {
	float: left;
	margin: 0 10px;
}
</style>

<div id="yii-debug-toolbar">
	<?php foreach ($panels as $panel): ?>
	<?php echo $panel->getSummary(); ?>
	<?php endforeach; ?>
</div>
