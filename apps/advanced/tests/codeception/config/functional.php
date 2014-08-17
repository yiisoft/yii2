<?php
/**
 * Application configuration shared by all applications functional tests
 */
return [
    'components' => [
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=yii2_advanced_acceptance',
        ],
    ],
];