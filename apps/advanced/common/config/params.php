<?php

Yii::setAlias('common', __DIR__ . '/../');
Yii::setAlias('frontend', __DIR__ . '/../../frontend');
Yii::setAlias('backend', __DIR__ . '/../../backend');

return array(
	'adminEmail' => 'admin@example.com',
	'supportEmail' => 'support@example.com',

	'components.cache' => array(
		'class' => 'yii\caching\FileCache',
	),

	'components.db' => array(
		'class' => 'yii\db\Connection',
		'dsn' => 'mysql:host=localhost;dbname=yii2advanced',
		'username' => 'root',
		'password' => '',
		'charset' => 'utf8',
	),
);
