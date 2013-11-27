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
	'language' => 'en-US',
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
				// Currently no translations for console exist but if one needs them they should follow the usual pattern 
				//'base' => [
				//	'class' => 'yii\i18n\PhpMessageSource',
				//	'basePath' => '@app/messages',
				//	'sourceLanguage' => 'en-US',
				//],
			],
		],
	'params' => $params,
];
