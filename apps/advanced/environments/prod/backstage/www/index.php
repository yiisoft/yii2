<?php
// comment out the following line to disable debug mode
defined('YII_DEBUG') or define('YII_DEBUG', false);

require(__DIR__ . '/../../vendor/yiisoft/yii2/yii/Yii.php');
require(__DIR__ . '/../../vendor/autoload.php');

$config = yii\helpers\ArrayHelper::merge(
	require(__DIR__ . '/../config/main.php'),
	require(__DIR__ . '/../config/main-local.php')
);

$application = new yii\web\Application($config);
$application->run();
