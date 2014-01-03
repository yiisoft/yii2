<?php

Yii::setAlias('tests', __DIR__ . '/../tests');

$params = require(__DIR__ . '/params.php');
return [
	'id' => 'basic-console',
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
		'db' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=localhost;dbname=yii2basic',
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
		],
		'fixture' => [
			'class' => 'yii\test\DbFixtureManager',
			'basePath' => '@tests/unit/fixtures',
		],
	],
	'params' => $params,
];
