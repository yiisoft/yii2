<?php

return array(
	'id' => 'bootstrap',
	'basePath' => dirname(__DIR__),
	'preload' => array('debug'),
	'modules' => array(
		'debug' => array(
			'class' => 'yii\debug\Module',
			'enabled' => YII_DEBUG && YII_ENV === 'dev',
		),
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
