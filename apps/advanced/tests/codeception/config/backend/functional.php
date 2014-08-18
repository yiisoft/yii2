<?php
$_SERVER['SCRIPT_FILENAME'] = YII_TEST_BACKEND_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = YII_BACKEND_TEST_ENTRY_URL;

return yii\helpers\ArrayHelper::merge(
    require(YII_ROOT_DIR . '/backend/config/main.php'),
    require(YII_ROOT_DIR . '/backend/config/main-local.php'),
    require(YII_ROOT_DIR . '/common/config/main.php'),
    require(YII_ROOT_DIR . '/common/config/main-local.php'),
    require(dirname(__DIR__) . '/config.php'),
    require(dirname(__DIR__) . '/functional.php'),
    require(__DIR__ . '/config.php'),
    [
    ]
);
