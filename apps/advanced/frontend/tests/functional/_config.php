<?php

// set correct script paths
$_SERVER['SCRIPT_FILENAME'] = $GLOBALS['TEST_ENTRY_FILE'];
$_SERVER['SCRIPT_NAME'] = $GLOBALS['TEST_ENTRY_URL'];

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
