<?php

return array(
	'id' => 'bootstrap',
	'basePath' => dirname(__DIR__),
	'preload' => array('debug'),
	'modules' => array(
		'debug' => array(
			'class' => 'yii\debug\Module',
			'enabled' => YII_ENV_DEV,
		),
	),
	'components' => array(
		'cache' => array(
			'class' => 'yii\caching\FileCache',
		),
		'user' => array(
			'identityClass' => 'app\models\User',
		),
		'errorHandler' => array(
			'errorAction' => 'site/error',
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
	),
	'params' => require(__DIR__ . '/params.php'),
);
