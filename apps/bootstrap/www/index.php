<?php

// comment out the following line to disable debug mode
defined('YII_DEBUG') or define('YII_DEBUG', true);

//require(__DIR__ . '/../vendor/yiisoft/yii2/yii/Yii.php');
//require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../../../framework/yii/Yii.php');

$config = require(__DIR__ . '/../config/main.php');

$application = new yii\web\Application($config);
$application->run();
