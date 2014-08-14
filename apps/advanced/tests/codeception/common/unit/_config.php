<?php

return yii\helpers\ArrayHelper::merge(
    require(ROOT_DIR . '/common/config/main.php'),
    require(ROOT_DIR . '/common/config/main-local.php'),
    require(__DIR__ . '/../_config.php'),
    [
        'components' => [
            'db' => [
                'dsn' => 'mysql:host=localhost;dbname=yii2_advanced_unit',
            ],
        ],
        'id' => 'app-common',
        'basePath' => dirname(__DIR__),
    ]
);
