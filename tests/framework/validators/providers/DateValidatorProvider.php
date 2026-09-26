<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\validators\providers;

/**
 * Data provider for {@see \yiiunit\framework\validators\DateValidatorTest} test cases.
 */
final class DateValidatorProvider
{
    /**
     * Provides ICU format dates that strict date format validation rejects.
     *
     * @phpstan-return list<array{string, string, bool}>
     */
    public static function strictDateFormatIntlFail(): array
    {
        return [
            ['yyyy-MM-dd', '13-Mar-19', true],
            ['yyyy-MM-dd', '13-March-19', true],
            ['yyyy-MM-dd', '13-03-19', true],
            ['yyyy-MM-dd', '13-3-19', true],
            ['yyyy-MM-dd', '13-003-19', true],
            ['yyyy-MM-dd', '0013-Mar-19', true],
            ['yyyy-MM-dd', '13-Mar-00019', true],
            ['yyyy-MM-dd', '0000-03-19', true],
        ];
    }

    /**
     * Provides ICU format dates that strict date format validation accepts.
     *
     * @phpstan-return list<array{string, string, bool}>
     */
    public static function strictDateFormatIntlPass(): array
    {
        return [
            ['yyyy-MM-dd', '0013-03-19', true],
            ['yyyy-MM-dd', '2013-03-19', true],
            ['yyyy-MM-dd', '0001-03-19', true],
        ];
    }

    /**
     * Provides `php:` format dates that strict date format validation rejects.
     *
     * @phpstan-return list<array{string, string, bool}>
     */
    public static function strictDateFormatPhpFail(): array
    {
        return [
            ['php:Y-m-d', '13-Mar-19', true],
            ['php:Y-m-d', '13-March-19', true],
            ['php:Y-m-d', '13-03-19', true],
            ['php:Y-m-d', '13-3-19', true],
            ['php:Y-m-d', '13-003-19', true],
            ['php:Y-m-d', '0013-Mar-19', true],
            ['php:Y-m-d', '13-Mar-00019', true],
        ];
    }

    /**
     * Provides `php:` format dates that strict date format validation accepts.
     *
     * @phpstan-return list<array{string, string, bool}>
     */
    public static function strictDateFormatPhpPass(): array
    {
        return [
            ['php:Y-m-d', '0013-03-19', true],
            ['php:Y-m-d', '2013-03-19', true],
            ['php:Y-m-d', '0001-03-19', true],
        ];
    }

    /**
     * Provides `timestampAttributeFormat` values with the expected timestamp for every pair of default and application
     * time zones.
     *
     * @phpstan-return list<array{string|null, string, string|int, string, string}>
     */
    public static function timestampFormats(): array
    {
        $return = [];
        foreach (self::timezones() as $appTz) {
            foreach (self::timezones() as $tz) {
                $return[] = ['yyyy-MM-dd', '2013-09-13', '2013-09-13', $tz[0], $appTz[0]];
                // regardless of timezone, a simple date input should always result in 00:00:00 time
                $return[] = ['yyyy-MM-dd HH:mm:ss', '2013-09-13', '2013-09-13 00:00:00', $tz[0], $appTz[0]];
                $return[] = ['php:Y-m-d', '2013-09-13', '2013-09-13', $tz[0], $appTz[0]];
                $return[] = ['php:Y-m-d H:i:s', '2013-09-13', '2013-09-13 00:00:00', $tz[0], $appTz[0]];
                $return[] = ['php:U', '2013-09-13', '1379030400', $tz[0], $appTz[0]];
                $return[] = [null, '2013-09-13', 1_379_030_400, $tz[0], $appTz[0]];
            }
        }

        return $return;
    }

    /**
     * Provides the default time zones the date tests run under.
     *
     * @phpstan-return list<array{string}>
     */
    public static function timezones(): array
    {
        return [
            ['UTC'],
            ['Europe/Berlin'],
            ['America/Jamaica'],
        ];
    }
}
