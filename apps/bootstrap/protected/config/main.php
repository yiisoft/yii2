<?php

return array(
	'id' => 'hello',
	'basePath' => dirname(__DIR__),
	'preload' => array('log'),
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
				'file' => array(
					'class' => 'yii\logging\FileTarget',
					'levels' => array('error', 'warning'),
				),
			),
		),
	),
	'params' => array(
		'adminEmail' => 'admin@example.com',
	),
);
