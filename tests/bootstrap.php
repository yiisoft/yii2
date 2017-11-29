<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

// ensure we get report on all possible php errors
error_reporting(-1);

define('YII_ENABLE_ERROR_HANDLER', false);
define('YII_DEBUG', true);
define('YII_ENV', 'test');
$_SERVER['SCRIPT_NAME'] = '/' . __DIR__;
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

// require composer autoloader if available
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($composerAutoload)) {
    // check another composer's of PHPUnit
    if (defined('PHPUNIT_COMPOSER_INSTALL') && (
            stream_resolve_include_path(PHPUNIT_COMPOSER_INSTALL) !=
            stream_resolve_include_path($composerAutoload))) {
        $autoloaderList = spl_autoload_functions();
        $phpUnitAutoloader = $autoloaderList[0][0];
        $phpUnitAutoloader->unregister();
    }
    require_once $composerAutoload;
    // PHPUnit autoload at top
    if (!empty($phpUnitAutoloader)) {
        $phpUnitAutoloader->register(true);
    }
}
require_once __DIR__ . '/../framework/Yii.php';

Yii::setAlias('@yiiunit', __DIR__);

require_once __DIR__ . '/compatibility.php';
require_once __DIR__ . '/TestCase.php';
