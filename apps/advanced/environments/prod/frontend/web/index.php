<?php
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');

require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/yii/Yii.php');
Yii::importNamespaces(require(__DIR__ . '/../../vendor/composer/autoload_namespaces.php'));

$config = yii\helpers\ArrayHelper::merge(
	require(__DIR__ . '/../config/main.php'),
	require(__DIR__ . '/../config/main-local.php')
);

$application = new yii\web\Application($config);
$application->run();
