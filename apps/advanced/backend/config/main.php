<?php
$rootDir = dirname(dirname(__DIR__));

$params = array_merge(
	require($rootDir . '/common/config/params.php'),
	require($rootDir . '/common/config/params-local.php'),
	require(__DIR__ . '/params.php'),
	require(__DIR__ . '/params-local.php')
);

return [
	'id' => 'app-backend',
	'basePath' => dirname(__DIR__),
	'language' => 'en-US',
	'vendorPath' => $rootDir . '/vendor',
	'preload' => ['log'],
	'controllerNamespace' => 'backend\controllers',
	'modules' => [],
	'extensions' => require($rootDir . '/vendor/yiisoft/extensions.php'),
	'components' => [
		'db' => $params['components.db'],
		'cache' => $params['components.cache'],
		'mail' => $params['components.mail'],
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
		'i18n' => [
			'translations' => [
				'yii' => [
					'class' => 'yii\i18n\PhpMessageSource',
					//'basePath' => $rootDir . '/vendor/yiisoft/yii2/yii/messages', // would actually be the correct base path - but yiisoft did not incorporate translations yet!
					'basePath' => '@common/messages', // the yii translations are currently in the common section of the application template
					'sourceLanguage' => 'en-US',
				],
				'common' => [
					'class' => 'yii\i18n\PhpMessageSource',
					'basePath' => '@common/messages',
					'sourceLanguage' => 'en-US',
				],
				'base' => [
					'class' => 'yii\i18n\PhpMessageSource',
					'basePath' => '@app/messages',
					'sourceLanguage' => 'en-US',
				],
			],
		],
	],
	'params' => $params,
];
