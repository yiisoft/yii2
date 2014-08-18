<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

defined('YII_ROOT_DIR') or define('YII_ROOT_DIR', dirname(dirname(dirname(__DIR__))));

defined('FRONTEND_ENTRY_URL') or define('FRONTEND_ENTRY_URL', \Codeception\Configuration::config()['config']['test_entry_url']);
defined('FRONTEND_ENTRY_FILE') or define('FRONTEND_ENTRY_FILE', YII_ROOT_DIR . '/frontend/web/index-test.php');

require_once(YII_ROOT_DIR . '/vendor/autoload.php');
require_once(YII_ROOT_DIR . '/vendor/yiisoft/yii2/Yii.php');
require(YII_ROOT_DIR . '/common/config/aliases.php');

// set correct script paths

// the entry script file path for functional and acceptance tests
$_SERVER['SCRIPT_FILENAME'] = FRONTEND_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = FRONTEND_ENTRY_URL;
$_SERVER['SERVER_NAME'] = 'localhost';

Yii::setAlias('@codeception', dirname(__DIR__));