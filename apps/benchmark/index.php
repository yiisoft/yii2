<?php

defined('YII_DEBUG') or define('YII_DEBUG', false);

require(__DIR__ . '/protected/vendor/yiisoft/yii2/yii/Yii.php');

$config = [
	'id' => 'benchmark',
	'basePath' => __DIR__ . '/protected',
	'components' => [
		'urlManager' => [
			'enablePrettyUrl' => true,
		],
	],
];

$application = new yii\web\Application($config);
$application->run();
