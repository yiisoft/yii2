<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * This is the configuration file for the Yii 2 unit tests.
 *
 * You can override configuration values by creating a `config.local.php` file
 * and manipulate the `$config` variable.
 * For example to change MySQL username and password your `config.local.php` should
 * contain the following:
 * ```php
 * <?php
 * $config['databases']['mysql']['username'] = 'yiitest';
 * $config['databases']['mysql']['password'] = 'changeme';
 * ```
 */
$config = [
    'databases' => [
        'cubrid' => [
            'dsn' => 'cubrid:dbname=demodb;host=localhost;port=33000',
            'username' => 'dba',
            'password' => '',
            'fixture' => __DIR__ . '/cubrid.sql',
        ],
        'mysql' => [
            'dsn' => 'mysql:host=127.0.0.1;dbname=yiitest',
            'username' => 'travis',
            'password' => '',
            'fixture' => __DIR__ . '/mysql.sql',
        ],
        'sqlite' => [
            'dsn' => 'sqlite::memory:',
            'fixture' => __DIR__ . '/sqlite.sql',
        ],
        'sqlsrv' => [
            'dsn' => 'sqlsrv:Server=localhost;Database=test',
            'username' => '',
            'password' => '',
            'fixture' => __DIR__ . '/mssql.sql',
        ],
        'pgsql' => [
            'dsn' => 'pgsql:host=localhost;dbname=yiitest;port=5432;',
            'username' => 'postgres',
            'password' => 'postgres',
            'fixture' => __DIR__ . '/postgres.sql',
        ],
        'oci' => [
            'dsn' => 'oci:dbname=LOCAL_XE;charset=AL32UTF8;',
            'username' => '',
            'password' => '',
            'fixture' => __DIR__ . '/oci.sql',
        ],
    ],
];

if (is_file(__DIR__ . '/config.local.php')) {
    include __DIR__ . '/config.local.php';
}

return $config;
