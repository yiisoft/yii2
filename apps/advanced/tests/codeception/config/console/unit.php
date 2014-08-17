<?php

return yii\helpers\ArrayHelper::merge(
    require(YII_ROOT_DIR . '/common/config/main.php'),
    require(YII_ROOT_DIR . '/common/config/main-local.php'),
    require(YII_ROOT_DIR . '/console/config/main.php'),
    require(YII_ROOT_DIR . '/console/config/main-local.php'),
    require(dirname(__DIR__) . '/config.php'),
    require(dirname(__DIR__) . '/unit.php'),
    [
    ]
);
