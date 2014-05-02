<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

defined('YIISOFT') or define('YIISOFT', __DIR__ );
// with the above change, YIISOFT which hold vendor folder can easily redirect to somewhere else like: 'G:\yii2\vendor\yiisoft'
require(YIISOFT . '/../../vendor/autoload.php');
require(YIISOFT . '/../../vendor/yiisoft/yii2/Yii.php');

require(__DIR__ . '/../../common/config/aliases.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/main.php'),
    require(__DIR__ . '/../../common/config/main-local.php'),
    require(__DIR__ . '/../config/main.php'),
    require(__DIR__ . '/../config/main-local.php')
);

$application = new yii\web\Application($config);
$application->run();
