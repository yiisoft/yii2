<?php
$rootDir = __DIR__ . '/../..';

$params = array_merge(
	require($rootDir . '/common/config/params.php'),
	require($rootDir . '/common/config/params-local.php'),
	require(__DIR__ . '/params.php'),
	require(__DIR__ . '/params-local.php')
);

return [
	'id' => 'app-backend',
	'basePath' => dirname(__DIR__),
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
	'preload' => ['log'],
	'controllerNamespace' => 'backend\controllers',
	'modules' => [],
	'extensions' => require(__DIR__ . '/../../vendor/yii-extensions.php'),
	'components' => [
		'request' => [
			'enableCsrfValidation' => true,
		],
		'db' => $params['components.db'],
		'cache' => $params['components.cache'],
		'user' => [
			'identityClass' => 'common\models\User',
		],
		'log' => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'errorHandler' => [
			'errorAction' => 'site/error',
		],
	],
	'params' => $params,
];
