<?php
$_SERVER['SCRIPT_FILENAME'] = FRONTEND_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = FRONTEND_ENTRY_URL;

return yii\helpers\ArrayHelper::merge(
    require(YII_APP_BASE_PATH . '/common/config/main.php'),
    require(YII_APP_BASE_PATH . '/common/config/main-local.php'),
    require(YII_APP_BASE_PATH . '/frontend/config/main.php'),
    require(YII_APP_BASE_PATH . '/frontend/config/main-local.php'),
    require(dirname(__DIR__) . '/config.php'),
    require(dirname(__DIR__) . '/functional.php'),
    require(__DIR__ . '/config.php'),
    [
    ]
);
