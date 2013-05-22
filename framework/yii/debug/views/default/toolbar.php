<?php use yii\helpers\Html;

echo Html::style("
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
");
?>

<div class="yii-debug-toolbar-block">
</div>

<div class="yii-debug-toolbar-block">
Peak memory: <?php echo sprintf('%.2fMB', $memory / 1048576); ?>
</div>

<div class="yii-debug-toolbar-block">
Time spent: <?php echo sprintf('%.3fs', $time); ?>
</div>

<div class="yii-debug-toolbar-block">
</div>

<div class="yii-debug-toolbar-block">
</div>

