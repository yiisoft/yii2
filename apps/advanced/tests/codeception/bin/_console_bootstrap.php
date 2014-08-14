<?php
/**
 * Yii console bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

// fcgi doesn't have STDIN and STDOUT defined by default
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));

defined('ROOT_DIR') or define('ROOT_DIR', dirname(dirname(dirname(__DIR__))));

require_once(ROOT_DIR . '/vendor/autoload.php');
require_once(ROOT_DIR . '/vendor/yiisoft/yii2/Yii.php');
require_once(ROOT_DIR . '/common/config/aliases.php');

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');
