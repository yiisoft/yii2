<?php

require(__DIR__ . '/../../../framework/yii.php');

$application = new yii\web\Application('test', __DIR__ . '/protected');
$application->run();
