<?php

if (!class_exists('yii\cs\YiisoftConfig', true)) {
    // TODO: change error message
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
            ->exclude('docs')
            ->exclude('apps')
            ->exclude('extensions')
            // requirement checker should work even on PHP 4.3, so it needs special treatment
            ->exclude('framework/requirements')
            ->notPath('framework/classes.php')
            ->notPath('framework/helpers/mimeTypes.php')
            ->notPath('framework/views/messageConfig.php')
    );
