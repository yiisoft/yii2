<?php

use MSpirkov\Yii2\Rector\Rules\AddPropertyTagsRector;
use MSpirkov\Yii2\Rector\Rules\RemoveRedundantPropertyTagsRector;
use Rector\Config\RectorConfig;
use yii\di\Container;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/build',
        __DIR__ . '/framework',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        __DIR__ . '/tests/data',
        '*/views/*',
        '*/stubs/*',
        '*/stub/*',
        '*/mocks/*',
        '*/mock/*',
        '*/enums/*',
    ])
    ->withConfiguredRule(AddPropertyTagsRector::class, [
        'skippedClasses' => [
            // It’s a bit weird to set singletons via assignment instead of using `setSingleton`
            Container::class => ['singleton']
        ],
    ])
    ->withRules([
        RemoveRedundantPropertyTagsRector::class,
    ]);
