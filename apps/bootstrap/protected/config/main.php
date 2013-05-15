<?php

return array(
	'id' => 'hello',
	'basePath' => dirname(__DIR__),
	'preload' => array('log'),
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
	'params' => array(
		'adminEmail' => 'admin@example.com',
	),
);
