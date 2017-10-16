<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

if (getenv('TRAVIS') == 'true' && PHP_VERSION_ID >= 70200 && PHP_VERSION_ID < 70300) {
    // The each() function is deprecated. This message will be suppressed on further calls
    // in /home/travis/build/yiisoft/yii2/vendor/phpunit/phpunit/src/Util/Getopt.php on line 38
    // ---
    // PHPUnit should be updated, but newer PHPUnit versions require PHP 7.
    // Solution: suspend deprecation messages on Travis for PHP 7.2
    error_reporting(E_ALL & ~E_DEPRECATED);
} else {
    error_reporting(-1); // ensure we get report on all possible php errors
}

define('YII_ENABLE_ERROR_HANDLER', false);
define('YII_DEBUG', true);
define('YII_ENV', 'test');
$_SERVER['SCRIPT_NAME'] = '/' . __DIR__;
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

// require composer autoloader if available
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}
require_once __DIR__ . '/../framework/Yii.php';

Yii::setAlias('@yiiunit', __DIR__);

require_once __DIR__ . '/compatibility.php';
require_once __DIR__ . '/TestCase.php';
