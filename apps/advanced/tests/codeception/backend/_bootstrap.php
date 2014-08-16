<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

defined('ROOT_DIR') or define('ROOT_DIR', dirname(dirname(dirname(__DIR__))));

defined('BACKEND_ENTRY_URL') or define('BACKEND_ENTRY_URL', \Codeception\Configuration::config()['config']['test_entry_url']);
defined('BACKEND_ENTRY_FILE') or define('BACKEND_ENTRY_FILE', ROOT_DIR . '/backend/web/index-test.php');

require_once(ROOT_DIR . '/vendor/autoload.php');
require_once(ROOT_DIR . '/vendor/yiisoft/yii2/Yii.php');
require(ROOT_DIR . '/common/config/aliases.php');

// set correct script paths

// the entry script file path for functional and acceptance tests
$_SERVER['SCRIPT_FILENAME'] = BACKEND_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = BACKEND_ENTRY_URL;
$_SERVER['SERVER_NAME'] = 'localhost';

Yii::setAlias('@codeception', dirname(__DIR__));
