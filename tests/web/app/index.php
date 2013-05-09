<?php

require(__DIR__ . '/../../../yii/Yii.php');

$application = new yii\web\Application('test', __DIR__ . '/protected');
$application->run();
