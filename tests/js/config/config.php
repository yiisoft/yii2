<?php

return [
    'id' => 'yii2-js-tests',
    'name' => 'Yii 2 framework JavaScript unit tests',
    'basePath' => dirname(__DIR__),
    'vendorPath' => __DIR__ . '/../../../vendor',
    'controllerNamespace' => 'tests\\js\\controllers',
    'components' => [
        'request' => [
            'enableCookieValidation' => false,
        ],
        'urlManager' => [
            'showScriptName' => false,
            'enablePrettyUrl' => true,
        ],
    ],
];
