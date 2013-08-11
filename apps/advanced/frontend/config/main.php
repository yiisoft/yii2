<?php
$rootDir = __DIR__ . '/../..';

$params = array_merge(
	require($rootDir . '/common/config/params.php'),
	require($rootDir . '/common/config/params-local.php'),
	require(__DIR__ . '/params.php'),
	require(__DIR__ . '/params-local.php')
);

return array(
	'id' => 'app-frontend',
	'basePath' => dirname(__DIR__),
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
	'controllerNamespace' => 'frontend\controllers',
	'modules' => array(
	),
	'components' => array(
		'db' => $params['components.db'],
		'cache' => $params['components.cache'],
		'user' => array(
			'class' => 'yii\web\User',
			'identityClass' => 'common\models\User',
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
