<?php

return yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../../console/config/main.php'),
    require(__DIR__ . '/../../../console/config/main-local.php'),
    require(__DIR__ . '/../../config/main.php'),
    require(__DIR__ . '/../../config/main-local.php'),
    [
        'components' => [
            'db' => [
                'dsn' => 'mysql:host=localhost;dbname=yii2_advanced_unit',
            ],
        ],
        'controllerMap' => [
            'fixture' => [
                'class' => 'yii\faker\FixtureController',
                'fixtureDataPath' => '@common/tests/unit/fixtures/data',
                'templatePath' => '@common/tests/templates/fixtures'
            ],
        ],
    ]
);
