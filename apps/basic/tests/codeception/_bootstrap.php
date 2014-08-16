<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

defined('TEST_ENTRY_URL') or define('TEST_ENTRY_URL', \Codeception\Configuration::config()['config']['test_entry_url']);
defined('TEST_ENTRY_FILE') or define('TEST_ENTRY_FILE', dirname(dirname(__DIR__)) . '/web/index-test.php');

require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');

$_SERVER['SCRIPT_FILENAME'] = TEST_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = TEST_ENTRY_URL;
$_SERVER['SERVER_NAME'] = 'localhost';

Yii::setAlias('@codeception', __DIR__);
