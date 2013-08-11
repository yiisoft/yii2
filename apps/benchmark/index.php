<?php

defined('YII_DEBUG') or define('YII_DEBUG', false);

require(__DIR__ . '/protected/vendor/yiisoft/yii2/yii/Yii.php');

$config = array(
	'id' => 'benchmark',
	'basePath' => __DIR__ . '/protected',
	'components' => array(
		'urlManager' => array(
			'enablePrettyUrl' => true,
		),
	)
);

$application = new yii\web\Application($config);
$application->run();
