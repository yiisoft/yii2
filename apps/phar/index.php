<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 10);

$pharPath = realpath(__DIR__ . '/../../yii') . '/yii.phar';
if (is_file($pharPath)) {
	require('phar://' . $pharPath . '/Yii.php');
	$config = require(__DIR__ . '/protected/config/main.php');
	$application = new \yii\web\Application($config);
	$application->run();
} else {
	echo 'Before using Yii2 PHAR package you should build it with PharController console command. ';
	echo 'See `build` directory for more details on how to do it.';
}
