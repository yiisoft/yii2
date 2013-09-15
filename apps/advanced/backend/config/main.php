<?php
$rootDir = __DIR__ . '/../..';

$params = array_merge(
	require($rootDir . '/common/config/params.php'),
	require($rootDir . '/common/config/params-local.php'),
	require(__DIR__ . '/params.php'),
	require(__DIR__ . '/params-local.php')
);

return array(
	'id' => 'app-backend',
	'basePath' => dirname(__DIR__),
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
	'preload' => array('log'),
	'controllerNamespace' => 'backend\controllers',
	'modules' => array(
	),
	'components' => array(
		'request' => array(
			'enableCsrfValidation' => true,
		),
		'db' => $params['components.db'],
		'cache' => $params['components.cache'],
		'user' => array(
			'identityClass' => 'common\models\User',
		),
		'log' => array(
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => array(
				array(
					'class' => 'yii\log\FileTarget',
					'levels' => array('error', 'warning'),
				),
			),
		),
		'errorHandler' => array(
			'errorAction' => 'site/error',
		),
	),
	'params' => $params,
);
