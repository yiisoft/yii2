<?php

define('YII_ENABLE_ERROR_HANDLER', false);
define('YII_DEBUG', true);
$_SERVER['SCRIPT_NAME'] = '/' . __DIR__;
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

require_once(__DIR__ . '/../../framework/yii/Yii.php');

Yii::setAlias('@yiiunit', __DIR__);

require_once(__DIR__ . '/TestCase.php');
