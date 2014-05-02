<?php

// NOTE: Make sure this file is not accessible when deployed to production
if (!in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('You are not allowed to access this file.');
}

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

defined('YIISOFT') or define('YIISOFT', __DIR__ );
// with the above change, YIISOFT which hold vendor folder can easily redirect to somewhere else like: 'G:\yii2\vendor\yiisoft'
require(YIISOFT . '/../../vendor/autoload.php');
require(YIISOFT . '/../../vendor/yiisoft/yii2/Yii.php');

require(__DIR__ . '/../../common/config/aliases.php');

$config = require(__DIR__ . '/../tests/acceptance/_config.php');

(new yii\web\Application($config))->run();
