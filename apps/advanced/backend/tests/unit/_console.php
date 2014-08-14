<?php

return yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../../common/config/main.php'),
    require(__DIR__ . '/../../../common/config/main-local.php'),
    require(__DIR__ . '/../../../console/config/main.php'),
    require(__DIR__ . '/../../../console/config/main-local.php'),
    [
        'components' => [
            'db' => [
                'dsn' => 'mysql:host=localhost;dbname=yii2_advanced_unit',
            ],
        ],
        'controllerMap' => [
            'fixture' => [
                'class' => 'yii\faker\FixtureController',
                'fixtureDataPath' => '@backend/tests/unit/fixtures/data',
                'templatePath' => '@commmon/tests/templates/fixtures'
            ],
        ],
    ]
);
