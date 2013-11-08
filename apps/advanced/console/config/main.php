<?php
$rootDir = __DIR__ . '/../..';

$params = array_merge(
	require($rootDir . '/common/config/params.php'),
	require($rootDir . '/common/config/params-local.php'),
	require(__DIR__ . '/params.php'),
	require(__DIR__ . '/params-local.php')
);

return [
	'id' => 'app-console',
	'basePath' => dirname(__DIR__),
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
	'controllerNamespace' => 'console\controllers',
	'modules' => [
	],
	'extensions' => require(__DIR__ . '/../../vendor/yiisoft/extensions.php'),
	'components' => [
		'db' => $params['components.db'],
		'cache' => $params['components.cache'],
		'mail' => $params['components.mail'],
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
