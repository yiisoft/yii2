<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'bowerPath' => dirname(dirname(__DIR__)) . '/bower_components',
    'extensions' => require(__DIR__ . '/../../vendor/yiisoft/extensions.php'),
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
];
