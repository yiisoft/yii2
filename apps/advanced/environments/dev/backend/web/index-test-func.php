<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . '/../../vendor/yiisoft/yii2/yii/Yii.php');

$config = yii\helpers\ArrayHelper::merge(
	require(__DIR__ . '/../config/main.php'),
	require(__DIR__ . '/../config/main-local.php'),
	require(__DIR__ . '/../config/codeception/functional.php')
);

$config['class'] = 'yii\web\Application';
return $config;
