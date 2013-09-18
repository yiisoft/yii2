<?php
return array(
	'databases' => array(
		'cubrid' => array(
			'dsn' => 'cubrid:dbname=demodb;host=localhost;port=33000',
			'username' => 'dba',
			'password' => '',
			'fixture' => __DIR__ . '/cubrid.sql',
		),
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
			'dsn' => 'pgsql:host=localhost;dbname=yiitest;port=5432;',
			'username' => 'postgres',
			'password' => 'postgres',
			'fixture' => __DIR__ . '/postgres.sql',
		),
		'redis' => array(
			'dsn' => 'redis://localhost:6379/0',
			'password' => null,
		),
	),
);
