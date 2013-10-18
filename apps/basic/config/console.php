<?php
$params = require(__DIR__ . '/params.php');
return [
	'id' => 'bootstrap-console',
	'basePath' => dirname(__DIR__),
	'preload' => array('log'),
	'controllerPath' => dirname(__DIR__) . '/commands',
	'controllerNamespace' => 'app\commands',
	'modules' => [
	],
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
