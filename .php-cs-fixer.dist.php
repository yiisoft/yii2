<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$header = '@link https://www.yiiframework.com/
@copyright Copyright (c) 2008 Yii Software LLC
@license https://www.yiiframework.com/license/';

$buildFinder = (new Finder())
    ->in(__DIR__ . '/build')
    ->notPath([
        '#(^|/)views/#',
    ]);

$frameworkFinder = (new Finder())
    ->in(__DIR__ . '/framework')
    ->notPath([
        '#^messages(/|$)#',
        '#(^|/)views/#',
        '#^classes\.php$#',
        '#^helpers/mimeAliases\.php$#',
        '#^helpers/mimeExtensions\.php$#',
        '#^helpers/mimeTypes\.php$#',
        '#^requirements/requirements\.php$#',
    ]);

$testsFinder = (new Finder())
    ->in(__DIR__ . '/tests')
    ->notPath([
        '#^data(/|$)#',
        '#(^|/)views/#',
        '#(^|/)stubs?/#',
        '#(^|/)mocks?/#',
        '#(^|/)enums/#',
    ]);

$finder = $buildFinder
    ->append($frameworkFinder)
    ->append($testsFinder);

return (new Config())
    ->setFinder($finder)
    ->setRules([
        'phpdoc_scalar' => true,
        'header_comment' => [
            'comment_type' => 'PHPDoc',
            'header' => $header,
            'location' => 'after_open',
        ],
    ]);
