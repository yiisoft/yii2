<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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
    require_once $composerAutoload;
}
require_once __DIR__ . '/../framework/Yii.php';

Yii::setAlias('@yiiunit', __DIR__);
Yii::setAlias('@root', dirname(__DIR__));

if (getenv('IS_LOCAL_TESTS')) {
    Yii::setAlias('@yiiunit/runtime', '/tmp/runtime');
    Yii::setAlias('@runtime', '/tmp/runtime');
}

require_once __DIR__ . '/TestCase.php';
