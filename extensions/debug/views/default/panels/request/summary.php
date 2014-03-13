<?php
use yii\helpers\Html;
use yii\web\Response;

/**
 * @var yii\debug\panels\RequestPanel $panel
 */

$statusCode = $panel->data['statusCode'];
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
    <a href="<?= $panel->getUrl() ?>" title="Status code: <?= $statusCode ?> <?= $statusText ?>">Status <span class="label <?= $class ?>"><?= $statusCode ?></span></a>
    <a href="<?= $panel->getUrl() ?>">Action <span class="label"><?= $panel->data['action'] ?></span></a>
</div>
