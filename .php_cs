<?php

if (!class_exists('PhpCsFixer\Config', true)) {
    fwrite(STDERR, "Your php-cs-version is outdated: please upgrade it.\n");
    die(16);
}

return PhpCsFixer\Config::create()
    ->setCacheFile(__DIR__ . '/.php_cs.cache')
    ->setRules([
        '@PSR2' => true,
        'braces' => [
            'allow_single_line_closure' => true,
        ],
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
    )
;
