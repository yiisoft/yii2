<?php

return array(
	'mysql' => array(
		'dsn' => 'mysql:host=127.0.0.1;dbname=yiitest',
		'username' => 'root',
		'password' => '',
		'fixture' => __DIR__ . '/mysql.sql',
	),
	'redis' => array(
		'dsn' => 'redis://localhost:6379/0',
		'password' => null,
//		'fixture' => __DIR__ . '/mysql.sql',
	),
);
