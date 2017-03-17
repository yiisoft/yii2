<?php

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
    require_once($composerAutoload);
}
require_once(__DIR__ . '/../framework/Yii.php');

Yii::setAlias('@yiiunit', __DIR__);

if (!class_exists('PHPUnit_Framework_TestCase') && class_exists('PHPUnit\Framework\TestCase')) {
    // compatibility with PHPUnit 6.x
    class PHPUnit_Framework_TestCase extends \PHPUnit\Framework\TestCase {}
}

require_once(__DIR__ . '/TestCase.php');
