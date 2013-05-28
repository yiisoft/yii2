<?php

return array(
	//'appClass' => '\yii\web\Application',
	'appClass' => '\yii\console\Application',
	'databases' => array(
		'mysql' => array(
			'dsn' => 'mysql:host=127.0.0.1;dbname=yiitest',
			'username' => 'travis',
			'password' => '',
			'fixture' => __DIR__ . '/mysql.sql',
		),
		'sqlite' => array(
			'dsn' => 'sqlite::memory:',
			'fixture' => __DIR__ . '/sqlite.sql',
		),
		'sqlsrv' => array(
			'dsn' => 'sqlsrv:Server=localhost;Database=test',
			'username' => '',
			'password' => '',
			'fixture' => __DIR__ . '/mssql.sql',
		),
                'pgsql' => array(
			'dsn' => 'pgsql:host=localhost;dbname=yiitest;port=5432',
			'username' => 'postgres',
			'password' => 'postgres',
			'fixture' => __DIR__ . '/postgres.sql',
                )
    )
);
