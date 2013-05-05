<?php
define('YII_DEBUG', true);

require __DIR__.'/../framework/yii.php';

$config = require dirname(__DIR__).'/protected/config/main.php';
$config['basePath'] = dirname(__DIR__).'/protected';

$app = new \yii\web\Application($config);
$app->run();
