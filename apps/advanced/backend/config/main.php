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
		'db' => $params['components.db'],
		'cache' => $params['components.cache'],
		'user' => array(
			'class' => 'yii\web\User',
			'identityClass' => 'common\models\User',
		),
		'assetManager' => array(
			'bundles' => require(__DIR__ . '/assets.php'),
		),
		'log' => array(
			'targets' => array(
				array(
					'class' => 'yii\log\FileTarget',
					'levels' => array('error', 'warning'),
				),
			),
		),
	),
	'params' => $params,
);
