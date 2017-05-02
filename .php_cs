<?php

if (!class_exists('yii\cs\YiisoftConfig', true)) {
    // @todo change error message
    fwrite(STDERR, "Your php-cs-version is outdated: please upgrade it.\n");
    die(16);
}

return yii\cs\YiisoftConfig::create()
    ->setCacheFile(__DIR__ . '/tests/runtime/php_cs.cache')
    ->mergeRules([
        'braces' => [
            'allow_single_line_closure' => true,
        ],
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
    )
;
