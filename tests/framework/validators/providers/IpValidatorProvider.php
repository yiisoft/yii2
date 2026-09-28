<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\validators\providers;

/**
 * Data provider for {@see \yiiunit\framework\validators\IpValidatorTest} test cases.
 */
final class IpValidatorProvider
{
    /**
     * Provides values that are not valid IP addresses, including non-string types.
     *
     * @phpstan-return list<array{mixed}>
     */
    public static function badIps(): array
    {
        return [['not.an.ip'], [['what an array', '??']], [123456], [true], [false], ['bad:forSure']];
    }

    /**
     * Provides `ranges` configurations with the expected list after alias substitution.
     *
     * @phpstan-return list<array{string|list<string>, list<string>}>
     */
    public static function rangesForSubstitution(): array
    {
        return [
            [
                '10.0.0.1',
                ['10.0.0.1'],
            ],
            [
                [
                    '192.168.0.32',
                    'fa::/32',
                    'any',
                ],
                [
                    '192.168.0.32',
                    'fa::/32',
                    '0.0.0.0/0',
                    '::/0',
                ],
            ],
            [
                [
                    '10.0.0.1',
                    '!private',
                ],
                [
                    '10.0.0.1',
                    '!10.0.0.0/8',
                    '!172.16.0.0/12',
                    '!192.168.0.0/16',
                    '!fd00::/8',
                ],
            ],
            [
                [
                    'private',
                    '!system',
                ],
                [
                    '10.0.0.0/8',
                    '172.16.0.0/12',
                    '192.168.0.0/16',
                    'fd00::/8',
                    '!224.0.0.0/4',
                    '!ff00::/8',
                    '!169.254.0.0/16',
                    '!fe80::/10',
                    '!127.0.0.0/8',
                    '!::1',
                    '!192.0.2.0/24',
                    '!198.51.100.0/24',
                    '!203.0.113.0/24',
                    '!2001:db8::/32',
                ]
            ],
        ];
    }
}
