<?php
$_SERVER['SCRIPT_FILENAME'] = YII_TEST_BACKEND_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = YII_BACKEND_TEST_ENTRY_URL;

return yii\helpers\ArrayHelper::merge(
    require(YII_APP_BASE_PATH . '/backend/config/main.php'),
    require(YII_APP_BASE_PATH . '/backend/config/main-local.php'),
    require(YII_APP_BASE_PATH . '/common/config/main.php'),
    require(YII_APP_BASE_PATH . '/common/config/main-local.php'),
    require(dirname(__DIR__) . '/config.php'),
    require(dirname(__DIR__) . '/functional.php'),
    require(__DIR__ . '/config.php'),
    [
    ]
);
