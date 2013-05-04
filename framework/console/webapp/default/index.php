<?php
define('YII_DEBUG', true);

require $yii=;

$config = require __DIR__.'/protected/config/main.php';
$config['basePath'] = __DIR__.'/protected';

$app = new \yii\web\Application($config);
$app->run();