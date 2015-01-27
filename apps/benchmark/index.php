<?php

defined('YII_DEBUG') or define('YII_DEBUG', false);

$config = [
    'id' => 'benchmark',
    'basePath' => __DIR__ . '/protected',
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
        ],
    ],
];

$application = new yii\web\Application($config);
$application->run();
