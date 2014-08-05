<?php

// set correct script paths
$_SERVER['SCRIPT_FILENAME'] = dirname(dirname(__DIR__)) . '/web/index-test.php';
$_SERVER['SCRIPT_NAME'] = \Codeception\Configuration::config()['config']['test_entry_url'];;

return yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../config/main.php'),
    require(__DIR__ . '/../../config/main-local.php'),
    require(__DIR__ . '/../../../common/config/main.php'),
    require(__DIR__ . '/../../../common/config/main-local.php'),
    require(__DIR__ . '/../_config.php'),
    [
        'components' => [
            'db' => [
                'dsn' => 'mysql:host=localhost;dbname=yii2_advanced_functional',
            ],
        ],
    ]
);
