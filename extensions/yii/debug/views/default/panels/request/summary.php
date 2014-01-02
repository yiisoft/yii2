<?php
use yii\helpers\Html;
use yii\web\Response;

$statusCode = $data['statusCode'];
if ($statusCode === null) {
	$statusCode = 200;
}
if ($statusCode >= 200 && $statusCode < 300) {
	$class = 'label-success';
} elseif ($statusCode >= 100 && $statusCode < 200) {
	$class = 'label-info';
} else {
	$class = 'label-important';
}
$statusText = Html::encode(isset(Response::$httpStatuses[$statusCode]) ? Response::$httpStatuses[$statusCode] : '');
?>
<div class="yii-debug-toolbar-block">
	<a href="<?php echo $panel->getUrl(); ?>" title="Status code: <?php echo $statusCode; ?> <?php echo $statusText; ?>">Status <span class="label <?php echo $class; ?>"><?php echo $statusCode; ?></span></a>
</div>
<div class="yii-debug-toolbar-block">
	<a href="<?php echo $panel->getUrl(); ?>">Action <span class="label"><?php echo $data['action']; ?></span></a>
</div>