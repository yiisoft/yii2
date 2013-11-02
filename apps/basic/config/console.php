<?php
$params = require(__DIR__ . '/params.php');
return [
	'id' => 'bootstrap-console',
	'basePath' => dirname(__DIR__),
	'preload' => ['log'],
	'controllerPath' => dirname(__DIR__) . '/commands',
	'controllerNamespace' => 'app\commands',
	'extensions' => require(__DIR__ . '/../vendor/yiisoft/extensions.php'),
	'components' => [
		'cache' => [
			'class' => 'yii\caching\FileCache',
		],
		'log' => [
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
	],
	'params' => $params,
];
