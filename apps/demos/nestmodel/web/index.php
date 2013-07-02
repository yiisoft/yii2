<?php

// comment out the following line to disable debug mode
defined('YII_DEBUG') or define('YII_DEBUG', true);

require(__DIR__ . '/../../../../framework/yii/Yii.php');
require(__DIR__ . '/../vendor/autoload.php');

$config = require(__DIR__ . '/../app/config/web.php');

$application = new yii\web\Application($config);
$application->run();
