<?php

// NOTE: Make sure this file is not accessable when deployed to production

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/yii/Yii.php');

$config = yii\helpers\ArrayHelper::merge(
	require(__DIR__ . '/../config/web.php'),
	require(__DIR__ . '/../config/codeception/acceptance.php')
);

$application = new yii\web\Application($config);
$application->run();
