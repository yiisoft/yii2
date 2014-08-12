<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);

defined('YII_ENV') or define('YII_ENV', 'test');

require_once(__DIR__ . '/../../vendor/autoload.php');

require_once(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');

require(__DIR__ . '/../../common/config/aliases.php');

// set correct script paths

// the entry script file path for functional and acceptance tests
$_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__) . '/web/index-test.php';
$_SERVER['SCRIPT_NAME'] = \Codeception\Configuration::config()['config']['test_entry_url'];
$_SERVER['SERVER_NAME'] = 'localhost';
