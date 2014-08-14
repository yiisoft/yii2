<?php

// set correct script paths
$_SERVER['SCRIPT_FILENAME'] = ROOT_DIR . '/frontend/web/index-test.php';
$_SERVER['SCRIPT_NAME'] = \Codeception\Configuration::config()['config']['test_entry_url'];;

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
