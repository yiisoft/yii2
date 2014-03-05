<?php

// the entry script URL (without host info) for functional and acceptance tests
// PLEASE ADJUST IT TO THE ACTUAL ENTRY SCRIPT URL
defined('TEST_ENTRY_URL') ?: define('TEST_ENTRY_URL', '/frontend/web/index-test.php');

// the entry script file path for functional and acceptance tests
defined('TEST_ENTRY_FILE') ?: define('TEST_ENTRY_FILE', dirname(__DIR__) . '/web/index-test.php');

defined('YII_DEBUG') ?: define('YII_DEBUG', true);

defined('YII_ENV') ?: define('YII_ENV', 'test');

require_once(__DIR__ . '/../../vendor/autoload.php');

require_once(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');

require(__DIR__ . '/../../common/config/aliases.php');

// set correct script paths
$_SERVER['SCRIPT_FILENAME'] = TEST_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = TEST_ENTRY_URL;
$_SERVER['SERVER_NAME'] = 'localhost';
