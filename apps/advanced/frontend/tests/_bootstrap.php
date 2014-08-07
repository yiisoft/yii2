<?php

// the entry script URL (without host info) for functional and acceptance tests
// PLEASE ADJUST IT TO THE ACTUAL ENTRY SCRIPT URL
$GLOBALS['TEST_ENTRY_URL'] = '/advanced/frontend/web/index-test.php';

// the entry script file path for functional and acceptance tests
$GLOBALS['TEST_ENTRY_FILE'] = dirname(__DIR__) . '/web/index-test.php';

defined('YII_DEBUG') or define('YII_DEBUG', true);

defined('YII_ENV') or define('YII_ENV', 'test');

require_once(__DIR__ . '/../../vendor/autoload.php');

require_once(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');

require(__DIR__ . '/../../common/config/aliases.php');

// set correct script paths
$_SERVER['SCRIPT_FILENAME'] = $GLOBALS['TEST_ENTRY_FILE'];
$_SERVER['SCRIPT_NAME'] = $GLOBALS['TEST_ENTRY_URL'];
$_SERVER['SERVER_NAME'] = 'localhost';
