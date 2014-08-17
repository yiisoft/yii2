<?php

return yii\helpers\ArrayHelper::merge(
    require(ROOT_DIR . '/common/config/main.php'),
    require(ROOT_DIR . '/common/config/main-local.php'),
    require(ROOT_DIR . '/console/config/main.php'),
    require(ROOT_DIR . '/console/config/main-local.php'),
    require(dirname(__DIR__) . '/config.php'),
    require(dirname(__DIR__) . '/unit.php'),
    [
    ]
);
