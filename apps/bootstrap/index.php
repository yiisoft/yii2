<?php
// Set to false to disable debug mode
defined('YII_DEBUG') or define('YII_DEBUG', true);

$frameworkPath = __DIR__ . '/../../framework/';
require($frameworkPath . 'yii.php');

$config = require(__DIR__ . '/protected/config/main.php');
$application = new yii\web\Application($config);

// Register Composer autoloader
@include $frameworkPath . '/vendor/autoload.php';

$application->run();
