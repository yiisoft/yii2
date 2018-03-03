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
 *
 * ```php
 * <?php
 * $config['databases']['mysql']['username'] = 'yiitest';
 * $config['databases']['mysql']['password'] = 'changeme';
 * ```
 */
$config = [
    'databases' => [
        'mysql' => [
            'dsn' => 'mysql:host=mysql;dbname=yiitest',
            'username' => 'travis',
            'password' => 'travis',
            'fixture' => __DIR__ . '/mysql.sql',
        ],
        'sqlite' => [
            'dsn' => 'sqlite::memory:',
            'fixture' => __DIR__ . '/sqlite.sql',
        ],
        'pgsql' => [
            'dsn' => 'pgsql:host=postgres;dbname=yiitest;port=5432;',
            'username' => 'postgres',
            'password' => 'postgres',
            'fixture' => __DIR__ . '/postgres.sql',
        ],
    ],
];

if (is_file(__DIR__ . '/config.local.php')) {
    include __DIR__ . '/config.local.php';
}

return $config;
