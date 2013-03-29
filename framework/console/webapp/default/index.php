<?php
define('YII_DEBUG', true);

$yii = __DIR__.'/../framework/yii.php';
require $yii;
$config = require dirname(__DIR__).'/protected/config/main.php';

$basePath = dirname(__DIR__).'/protected';
$app = new \yii\web\Application('webapp', $basePath, $config);
$app->run();