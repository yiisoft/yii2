<?php

return array(
	'adminEmail' => 'admin@example.com',

	'components.cache' => array(
		'class' => 'yii\caching\FileCache',
	),

	'components.db' => array(
		'class' => 'yii\db\Connection',
		'dsn' => 'mysql:host=localhost;dbname=yii2advanced',
		'username' => 'root',
		'password' => '',
	),
);