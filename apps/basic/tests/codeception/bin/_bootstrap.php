<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

// fcgi doesn't have STDIN and STDOUT defined by default
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));

defined('YII_ROOT_DIR') or define('YII_ROOT_DIR', dirname(dirname(dirname(__DIR__))));

require(YII_ROOT_DIR . '/vendor/autoload.php');
require(YII_ROOT_DIR . '/vendor/yiisoft/yii2/Yii.php');
