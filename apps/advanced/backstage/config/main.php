<?php
$rootDir = __DIR__ . '/../..';

$params = array_merge(
	require($rootDir . '/common/config/params.php'),
	require($rootDir . '/common/config/params-local.php'),
	require(__DIR__ . '/params.php'),
	require(__DIR__ . '/params-local.php')
);

return array(
	'id' => 'change-me',
	'basePath' => dirname(__DIR__),
	'preload' => array('log'),
	'controllerNamespace' => 'app\controllers',
	'modules' => array(
//		'debug' => array(
//			'class' => 'yii\debug\Module',
//		)
	),
	'components' => array(
		'cache' => array(
			'class' => 'yii\caching\FileCache',
		),
		'user' => array(
			'class' => 'yii\web\User',
			'identityClass' => 'app\models\User',
		),
		'assetManager' => array(
			'bundles' => require(__DIR__ . '/assets.php'),
		),
		'log' => array(
			'class' => 'yii\logging\Router',
			'targets' => array(
				array(
					'class' => 'yii\logging\FileTarget',
					'levels' => array('error', 'warning'),
				),
//				array(
//					'class' => 'yii\logging\DebugTarget',
//				)
			),
		),
	),
	'params' => $params,
);
