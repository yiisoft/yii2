<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);

require(__DIR__ . '/../../framework/yii.php');

$config = require(__DIR__ . '/protected/config/main.php');
$application = new yii\web\Application($config);
$application->run();
