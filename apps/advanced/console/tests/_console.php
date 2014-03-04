<?php
/**
 * Yii console bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

// fcgi doesn't have STDIN and STDOUT defined by default
defined('STDIN') ?: define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') ?: define('STDOUT', fopen('php://stdout', 'w'));

require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');
require_once(__DIR__ . '/../../common/config/aliases.php');

defined('YII_DEBUG') ?: define('YII_DEBUG', true);
defined('YII_ENV') ?: define('YII_ENV', 'test');
