<?php

return array(
	//'appClass' => '\yii\web\Application',
	'appClass' => '\yii\console\Application',
	'mysql' => array(
		'dsn' => 'mysql:host=127.0.0.1;dbname=yiitest',
		'username' => 'travis',
		'password' => '',
		'fixture' => __DIR__ . '/mysql.sql',
	),
);
