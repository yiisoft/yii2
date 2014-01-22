<?php
return [
	'preload' => [
		//'debug',
	],
	'modules' => [
		//		'debug' => 'yii\debug\Module',
		//		'gii' => 'yii\gii\Module',
	],
	'components' => [
		'db' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=localhost;dbname=yii2advanced',
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
		],
	],
];
