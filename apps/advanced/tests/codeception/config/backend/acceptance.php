<?php
defined('ROOT_DIR') or define('ROOT_DIR', dirname(dirname(dirname(dirname(__DIR__)))));
return yii\helpers\ArrayHelper::merge(
    require(ROOT_DIR . '/common/config/main.php'),
    require(ROOT_DIR . '/common/config/main-local.php'),
    require(ROOT_DIR . '/backend/config/main.php'),
    require(ROOT_DIR . '/backend/config/main-local.php'),
    require(dirname(__DIR__) . '/config.php'),
    require(dirname(__DIR__) . '/acceptance.php'),
    require(__DIR__ . '/config.php'),
    [
    ]
);
