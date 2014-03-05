<?php

defined('YII_DEBUG') ?: define('YII_DEBUG', true);

defined('YII_ENV') ?: define('YII_ENV', 'test');

// fcgi doesn't have STDIN and STDOUT defined by default
defined('STDIN') ?: define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') ?: define('STDOUT', fopen('php://stdout', 'w'));

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
