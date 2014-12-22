<?php

require(__DIR__ . '/../../../framework/Yii.php');

$application = new yii\web\Application('test', __DIR__ . '/protected');
$application->run();
