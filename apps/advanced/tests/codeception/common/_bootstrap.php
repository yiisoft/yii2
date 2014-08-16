<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

defined('ROOT_DIR') or define('ROOT_DIR', dirname(dirname(dirname(__DIR__))));

require_once(ROOT_DIR . '/vendor/autoload.php');
require_once(ROOT_DIR . '/vendor/yiisoft/yii2/Yii.php');
require(ROOT_DIR . '/common/config/aliases.php');

// set correct script paths
$_SERVER['SERVER_NAME'] = 'localhost';

Yii::setAlias('@codeception', dirname(__DIR__));