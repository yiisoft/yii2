<?php
$_SERVER['SCRIPT_FILENAME'] = BACKEND_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = BACKEND_ENTRY_URL;

return yii\helpers\ArrayHelper::merge(
    require(ROOT_DIR . '/backend/config/main.php'),
    require(ROOT_DIR . '/backend/config/main-local.php'),
    require(ROOT_DIR . '/common/config/main.php'),
    require(ROOT_DIR . '/common/config/main-local.php'),
    require(dirname(__DIR__) . '/config.php'),
    require(dirname(__DIR__) . '/functional.php'),
    require(__DIR__ . '/config.php'),
    [
    ]
);
