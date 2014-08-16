<?php
$_SERVER['SCRIPT_FILENAME'] = FRONTEND_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = FRONTEND_ENTRY_URL;

return yii\helpers\ArrayHelper::merge(
    require(ROOT_DIR . '/frontend/config/main.php'),
    require(ROOT_DIR . '/frontend/config/main-local.php'),
    require(ROOT_DIR . '/common/config/main.php'),
    require(ROOT_DIR . '/common/config/main-local.php'),
    require(__DIR__ . '/../_config.php'),
    [
        'components' => [
            'db' => [
                'dsn' => 'mysql:host=localhost;dbname=yii2_advanced_functional',
            ],
        ],
    ]
);
