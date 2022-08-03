<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\helpers;

use IntlDateFormatter;
use Yii;

/**
 * BaseFormatConverter provides concrete implementation for [[FormatConverter]].
 *
 * Do not use BaseFormatConverter. Use [[FormatConverter]] instead.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @author Enrica Ruedin <e.ruedin@guggach.com>
 * @since 2.0
 */
class BaseFormatConverter
{
    /**
     * @var array the php fallback definition to use for the ICU short patterns `short`, `medium`, `long` and `full`.
     * This is used as fallback when the intl extension is not installed.
     */
    public static $phpFallbackDatePatterns = [
        'short' => [
            'date' => 'n/j/y',
            'time' => 'H:i',
            'datetime' => 'n/j/y H:i',
        ],
        'medium' => [
            'date' => 'M j, Y',
            'time' => 'g:i:s A',
            'datetime' => 'M j, Y g:i:s A',
        ],
        'long' => [
            'date' => 'F j, Y',
            'time' => 'g:i:sA',
            'datetime' => 'F j, Y g:i:sA',
        ],
        'full' => [
            'date' => 'l, F j, Y',
            'time' => 'g:i:sA T',
            'datetime' => 'l, F j, Y g:i:sA T',
        ],
    ];
    /**
     * @var array the jQuery UI fallback definition to use for the ICU short patterns `short`, `medium`, `long` and `full`.
     * This is used as fallback when the intl extension is not installed.
     */
    public static $juiFallbackDatePatterns = [
        'short' => [
            'date' => 'd/m/y',
            'time' => '',
            'datetime' => 'd/m/y',
        ],
        'medium' => [
            'date' => 'M d, yy',
            'time' => '',
            'datetime' => 'M d, yy',
        ],
        'long' => [
            'date' => 'MM d, yy',
            'time' => '',
            'datetime' => 'MM d, yy',
        ],
        'full' => [
            'date' => 'DD, MM d, yy',
            'time' => '',
            'datetime' => 'DD, MM d, yy',
        ],
    ];

    private static $_icuShortFormats = [
        'short' => 3, // IntlDateFormatter::SHORT,
        'medium' => 2, // IntlDateFormatter::MEDIUM,
        'long' => 1, // IntlDateFormatter::LONG,
        'full' => 0, // IntlDateFormatter::FULL,
    ];


    /**
     * Converts a date format pattern from [ICU format][] to [php date() function format][].
     *
     * The conversion is limited to date patterns that do not use escaped characters.
     * Patterns like `d 'of' MMMM yyyy` which will result in a date like `1 of December 2014` may not be converted correctly
     * because of the use of escaped characters.
     *
     * Pattern constructs that are not supported by the PHP format will be removed.
     *
     * [php date() function format]: https://www.php.net/manual/en/function.date.php
     * [ICU format]: https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
     *
     * @param string $pattern date format pattern in ICU format.
     * @param string $type 'date', 'time', or 'datetime'.
     * @param string|null $locale the locale to use for converting ICU short patterns `short`, `medium`, `long` and `full`.
     * If not given, `Yii::$app->language` will be used.
     * @return string The converted date format pattern.
     */
    public static function convertDateIcuToPhp($pattern, $type = 'date', $locale = null)
    {
        if (isset(self::$_icuShortFormats[$pattern])) {
            if (extension_loaded('intl')) {
                if ($locale === null) {
                    $locale = Yii::$app->language;
                }
                if ($type === 'date') {
                    $formatter = new IntlDateFormatter($locale, self::$_icuShortFormats[$pattern], IntlDateFormatter::NONE);
                } elseif ($type === 'time') {
                    $formatter = new IntlDateFormatter($locale, IntlDateFormatter::NONE, self::$_icuShortFormats[$pattern]);
                } else {
                    $formatter = new IntlDateFormatter($locale, self::$_icuShortFormats[$pattern], self::$_icuShortFormats[$pattern]);
                }
                $pattern = $formatter->getPattern();
            } else {
                return static::$phpFallbackDatePatterns[$pattern][$type];
            }
        }
        // https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
        // escaped text
        $escaped = [];
        if (preg_match_all('/(?<!\')\'(.*?[^\'])\'(?!\')/', $pattern, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $match[1] = str_replace('\'\'', '\'', $match[1]);
                $escaped[$match[0]] = '\\' . implode('\\', preg_split('//u', $match[1], -1, PREG_SPLIT_NO_EMPTY));
            }
        }

        return strtr($pattern, array_merge($escaped, [
            "''" => "\\'",  // two single quotes produce one
            'G' => '',      // era designator like (Anno Domini)
            'Y' => 'o',     // 4digit year of "Week of Year"
            'y' => 'Y',     // 4digit year e.g. 2014
            'yyyy' => 'Y',  // 4digit year e.g. 2014
            'yy' => 'y',    // 2digit year number eg. 14
            'u' => '',      // extended year e.g. 4601
            'U' => '',      // cyclic year name, as in Chinese lunar calendar
            'r' => '',      // related Gregorian year e.g. 1996
            'Q' => '',      // number of quarter
            'QQ' => '',     // number of quarter '02'
            'QQQ' => '',    // quarter 'Q2'
            'QQQQ' => '',   // quarter '2nd quarter'
            'QQQQQ' => '',  // number of quarter '2'
            'q' => '',      // number of Stand Alone quarter
            'qq' => '',     // number of Stand Alone quarter '02'
            'qqq' => '',    // Stand Alone quarter 'Q2'
            'qqqq' => '',   // Stand Alone quarter '2nd quarter'
            'qqqqq' => '',  // number of Stand Alone quarter '2'
            'M' => 'n',     // Numeric representation of a month, without leading zeros
            'MM' => 'm',    // Numeric representation of a month, with leading zeros
            'MMM' => 'M',   // A short textual representation of a month, three letters
            'MMMM' => 'F',  // A full textual representation of a month, such as January or March
            'MMMMM' => '',
            'L' => 'n',     // Stand alone month in year
            'LL' => 'm',    // Stand alone month in year
            'LLL' => 'M',   // Stand alone month in year
            'LLLL' => 'F',  // Stand alone month in year
            'LLLLL' => '',  // Stand alone month in year
            'w' => 'W',     // ISO-8601 week number of year
            'ww' => 'W',    // ISO-8601 week number of year
            'W' => '',      // week of the current month
            'd' => 'j',     // day without leading zeros
            'dd' => 'd',    // day with leading zeros
            'D' => 'z',     // day of the year 0 to 365
            'F' => '',      // Day of Week in Month. eg. 2nd Wednesday in July
            'g' => '',      // Modified Julian day. This is different from the conventional Julian day number in two regards.
            'E' => 'D',     // day of week written in short form eg. Sun
            'EE' => 'D',
            'EEE' => 'D',
            'EEEE' => 'l',  // day of week fully written eg. Sunday
            'EEEEE' => '',
            'EEEEEE' => '',
            'e' => 'N',     // ISO-8601 numeric representation of the day of the week 1=Mon to 7=Sun
            'ee' => 'N',    // php 'w' 0=Sun to 6=Sat isn't supported by ICU -> 'w' means week number of year
            'eee' => 'D',
            'eeee' => 'l',
            'eeeee' => '',
            'eeeeee' => '',
            'c' => 'N',     // ISO-8601 numeric representation of the day of the week 1=Mon to 7=Sun
            'cc' => 'N',    // php 'w' 0=Sun to 6=Sat isn't supported by ICU -> 'w' means week number of year
            'ccc' => 'D',
            'cccc' => 'l',
            'ccccc' => '',
            'cccccc' => '',
            'a' => 'A',     // AM/PM marker
            'h' => 'g',     // 12-hour format of an hour without leading zeros 1 to 12h
            'hh' => 'h',    // 12-hour format of an hour with leading zeros, 01 to 12 h
            'H' => 'G',     // 24-hour format of an hour without leading zeros 0 to 23h
            'HH' => 'H',    // 24-hour format of an hour with leading zeros, 00 to 23 h
            'k' => '',      // hour in day (1~24)
            'kk' => '',     // hour in day (1~24)
            'K' => '',      // hour in am/pm (0~11)
            'KK' => '',     // hour in am/pm (0~11)
            'm' => 'i',     // Minutes without leading zeros, not supported by php but we fallback
            'mm' => 'i',    // Minutes with leading zeros
            's' => 's',     // Seconds, without leading zeros, not supported by php but we fallback
            'ss' => 's',    // Seconds, with leading zeros
            'S' => '',      // fractional second
            'SS' => '',     // fractional second
            'SSS' => '',    // fractional second
            'SSSS' => '',   // fractional second
            'A' => '',      // milliseconds in day
            'z' => 'T',     // Timezone abbreviation
            'zz' => 'T',    // Timezone abbreviation
            'zzz' => 'T',   // Timezone abbreviation
            'zzzz' => 'T',  // Timezone full name, not supported by php but we fallback
            'Z' => 'O',     // Difference to Greenwich time (GMT) in hours
            'ZZ' => 'O',    // Difference to Greenwich time (GMT) in hours
            'ZZZ' => 'O',   // Difference to Greenwich time (GMT) in hours
            'ZZZZ' => '\G\M\TP', // Time Zone: long localized GMT (=OOOO) e.g. GMT-08:00
            'ZZZZZ' => '',  //  TIme Zone: ISO8601 extended hms? (=XXXXX)
            'O' => '',      // Time Zone: short localized GMT e.g. GMT-8
            'OOOO' => '\G\M\TP', //  Time Zone: long localized GMT (=ZZZZ) e.g. GMT-08:00
            'v' => '\G\M\TP', // Time Zone: generic non-location (falls back first to VVVV and then to OOOO) using the ICU defined fallback here
            'vvvv' => '\G\M\TP', // Time Zone: generic non-location (falls back first to VVVV and then to OOOO) using the ICU defined fallback here
            'V' => '',      // Time Zone: short time zone ID
            'VV' => 'e',    // Time Zone: long time zone ID
            'VVV' => '',    // Time Zone: time zone exemplar city
            'VVVV' => '\G\M\TP', // Time Zone: generic location (falls back to OOOO) using the ICU defined fallback here
            'X' => '',      // Time Zone: ISO8601 basic hm?, with Z for 0, e.g. -08, +0530, Z
            'XX' => 'O, \Z', // Time Zone: ISO8601 basic hm, with Z, e.g. -0800, Z
            'XXX' => 'P, \Z',    // Time Zone: ISO8601 extended hm, with Z, e.g. -08:00, Z
            'XXXX' => '',   // Time Zone: ISO8601 basic hms?, with Z, e.g. -0800, -075258, Z
            'XXXXX' => '',  // Time Zone: ISO8601 extended hms?, with Z, e.g. -08:00, -07:52:58, Z
            'x' => '',      // Time Zone: ISO8601 basic hm?, without Z for 0, e.g. -08, +0530
            'xx' => 'O',    // Time Zone: ISO8601 basic hm, without Z, e.g. -0800
            'xxx' => 'P',   // Time Zone: ISO8601 extended hm, without Z, e.g. -08:00
            'xxxx' => '',   // Time Zone: ISO8601 basic hms?, without Z, e.g. -0800, -075258
            'xxxxx' => '',  // Time Zone: ISO8601 extended hms?, without Z, e.g. -08:00, -07:52:58
        ]));
    }

    /**
     * Converts a date format pattern from [php date() function format][] to [ICU format][].
     *
     * Pattern constructs that are not supported by the ICU format will be removed.
     *
     * [php date() function format]: https://www.php.net/manual/en/function.date.php
     * [ICU format]: https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
     *
     * Since 2.0.13 it handles escaped characters correctly.
     *
     * @param string $pattern date format pattern in php date()-function format.
     * @return string The converted date format pattern.
     */
    public static function convertDatePhpToIcu($pattern)
    {
        // https://www.php.net/manual/en/function.date.php
        $result = strtr($pattern, [
            "'" => "''''",  // single `'` should be encoded as `''`, which internally should be encoded as `''''`
            // Day
            '\d' => "'d'",
            'd' => 'dd',    // Day of the month, 2 digits with leading zeros 	01 to 31
            '\D' => "'D'",
            'D' => 'eee',   // A textual representation of a day, three letters 	Mon through Sun
            '\j' => "'j'",
            'j' => 'd',     // Day of the month without leading zeros 	1 to 31
            '\l' => "'l'",
            'l' => 'eeee',  // A full textual representation of the day of the week 	Sunday through Saturday
            '\N' => "'N'",
            'N' => 'e',     // ISO-8601 numeric representation of the day of the week, 1 (for Monday) through 7 (for Sunday)
            '\S' => "'S'",
            'S' => '',      // English ordinal suffix for the day of the month, 2 characters 	st, nd, rd or th. Works well with j
            '\w' => "'w'",
            'w' => '',      // Numeric representation of the day of the week 	0 (for Sunday) through 6 (for Saturday)
            '\z' => "'z'",
            'z' => 'D',     // The day of the year (starting from 0) 	0 through 365
            // Week
            '\W' => "'W'",
            'W' => 'w',     // ISO-8601 week number of year, weeks starting on Monday (added in PHP 4.1.0) 	Example: 42 (the 42nd week in the year)
            // Month
            '\F' => "'F'",
            'F' => 'MMMM',  // A full textual representation of a month, January through December
            '\m' => "'m'",
            'm' => 'MM',    // Numeric representation of a month, with leading zeros 	01 through 12
            '\M' => "'M'",
            'M' => 'MMM',   // A short textual representation of a month, three letters 	Jan through Dec
            '\n' => "'n'",
            'n' => 'M',     // Numeric representation of a month, without leading zeros 	1 through 12, not supported by ICU but we fallback to "with leading zero"
            '\t' => "'t'",
            't' => '',      // Number of days in the given month 	28 through 31
            // Year
            '\L' => "'L'",
            'L' => '',      // Whether it's a leap year, 1 if it is a leap year, 0 otherwise.
            '\o' => "'o'",
            'o' => 'Y',     // ISO-8601 year number. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead.
            '\Y' => "'Y'",
            'Y' => 'yyyy',  // A full numeric representation of a year, 4 digits 	Examples: 1999 or 2003
            '\y' => "'y'",
            'y' => 'yy',    // A two digit representation of a year 	Examples: 99 or 03
            // Time
            '\a' => "'a'",
            'a' => 'a',     // Lowercase Ante meridiem and Post meridiem, am or pm
            '\A' => "'A'",
            'A' => 'a',     // Uppercase Ante meridiem and Post meridiem, AM or PM, not supported by ICU but we fallback to lowercase
            '\B' => "'B'",
            'B' => '',      // Swatch Internet time 	000 through 999
            '\g' => "'g'",
            'g' => 'h',     // 12-hour format of an hour without leading zeros 	1 through 12
            '\G' => "'G'",
            'G' => 'H',     // 24-hour format of an hour without leading zeros 0 to 23h
            '\h' => "'h'",
            'h' => 'hh',    // 12-hour format of an hour with leading zeros, 01 to 12 h
            '\H' => "'H'",
            'H' => 'HH',    // 24-hour format of an hour with leading zeros, 00 to 23 h
            '\i' => "'i'",
            'i' => 'mm',    // Minutes with leading zeros 	00 to 59
            '\s' => "'s'",
            's' => 'ss',    // Seconds, with leading zeros 	00 through 59
            '\u' => "'u'",
            'u' => '',      // Microseconds. Example: 654321
            // Timezone
            '\e' => "'e'",
            'e' => 'VV',    // Timezone identifier. Examples: UTC, GMT, Atlantic/Azores
            '\I' => "'I'",
            'I' => '',      // Whether or not the date is in daylight saving time, 1 if Daylight Saving Time, 0 otherwise.
            '\O' => "'O'",
            'O' => 'xx',    // Difference to Greenwich time (GMT) in hours, Example: +0200
            '\P' => "'P'",
            'P' => 'xxx',   // Difference to Greenwich time (GMT) with colon between hours and minutes, Example: +02:00
            '\T' => "'T'",
            'T' => 'zzz',   // Timezone abbreviation, Examples: EST, MDT ...
            '\Z' => "'Z'",
            'Z' => '',      // Timezone offset in seconds. The offset for timezones west of UTC is always negative, and for those east of UTC is always positive. -43200 through 50400
            // Full Date/Time
            '\c' => "'c'",
            'c' => "yyyy-MM-dd'T'HH:mm:ssxxx", // ISO 8601 date, e.g. 2004-02-12T15:19:21+00:00
            '\r' => "'r'",
            'r' => 'eee, dd MMM yyyy HH:mm:ss xx', // RFC 2822 formatted date, Example: Thu, 21 Dec 2000 16:01:07 +0200
            '\U' => "'U'",
            'U' => '',      // Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
            '\\\\' => '\\',
        ]);

        // remove `''` - they're result of consecutive escaped chars (`\A\B` will be `'A''B'`, but should be `'AB'`)
        // real `'` are encoded as `''''`
        return strtr($result, [
            "''''" => "''",
            "''" => '',
        ]);
    }

    /**
     * Converts a date format pattern from [ICU format][] to [jQuery UI date format][].
     *
     * Pattern constructs that are not supported by the jQuery UI format will be removed.
     *
     * [jQuery UI date format]: https://api.jqueryui.com/datepicker/#utility-formatDate
     * [ICU format]: https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
     *
     * @param string $pattern date format pattern in ICU format.
     * @param string $type 'date', 'time', or 'datetime'.
     * @param string|null $locale the locale to use for converting ICU short patterns `short`, `medium`, `long` and `full`.
     * If not given, `Yii::$app->language` will be used.
     * @return string The converted date format pattern.
     */
    public static function convertDateIcuToJui($pattern, $type = 'date', $locale = null)
    {
        if (isset(self::$_icuShortFormats[$pattern])) {
            if (extension_loaded('intl')) {
                if ($locale === null) {
                    $locale = Yii::$app->language;
                }
                if ($type === 'date') {
                    $formatter = new IntlDateFormatter($locale, self::$_icuShortFormats[$pattern], IntlDateFormatter::NONE);
                } elseif ($type === 'time') {
                    $formatter = new IntlDateFormatter($locale, IntlDateFormatter::NONE, self::$_icuShortFormats[$pattern]);
                } else {
                    $formatter = new IntlDateFormatter($locale, self::$_icuShortFormats[$pattern], self::$_icuShortFormats[$pattern]);
                }
                $pattern = $formatter->getPattern();
            } else {
                return static::$juiFallbackDatePatterns[$pattern][$type];
            }
        }
        // https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
        // escaped text
        $escaped = [];
        if (preg_match_all('/(?<!\')\'.*?[^\']\'(?!\')/', $pattern, $matches)) {
            foreach ($matches[0] as $match) {
                $escaped[$match] = $match;
            }
        }

        return strtr($pattern, array_merge($escaped, [
            'G' => '',      // era designator like (Anno Domini)
            'Y' => '',      // 4digit year of "Week of Year"
            'y' => 'yy',    // 4digit year e.g. 2014
            'yyyy' => 'yy', // 4digit year e.g. 2014
            'yy' => 'y',    // 2digit year number eg. 14
            'u' => '',      // extended year e.g. 4601
            'U' => '',      // cyclic year name, as in Chinese lunar calendar
            'r' => '',      // related Gregorian year e.g. 1996
            'Q' => '',      // number of quarter
            'QQ' => '',     // number of quarter '02'
            'QQQ' => '',    // quarter 'Q2'
            'QQQQ' => '',   // quarter '2nd quarter'
            'QQQQQ' => '',  // number of quarter '2'
            'q' => '',      // number of Stand Alone quarter
            'qq' => '',     // number of Stand Alone quarter '02'
            'qqq' => '',    // Stand Alone quarter 'Q2'
            'qqqq' => '',   // Stand Alone quarter '2nd quarter'
            'qqqqq' => '',  // number of Stand Alone quarter '2'
            'M' => 'm',     // Numeric representation of a month, without leading zeros
            'MM' => 'mm',   // Numeric representation of a month, with leading zeros
            'MMM' => 'M',   // A short textual representation of a month, three letters
            'MMMM' => 'MM', // A full textual representation of a month, such as January or March
            'MMMMM' => '',
            'L' => 'm',     // Stand alone month in year
            'LL' => 'mm',   // Stand alone month in year
            'LLL' => 'M',   // Stand alone month in year
            'LLLL' => 'MM', // Stand alone month in year
            'LLLLL' => '',  // Stand alone month in year
            'w' => '',      // ISO-8601 week number of year
            'ww' => '',     // ISO-8601 week number of year
            'W' => '',      // week of the current month
            'd' => 'd',     // day without leading zeros
            'dd' => 'dd',   // day with leading zeros
            'D' => 'o',     // day of the year 0 to 365
            'F' => '',      // Day of Week in Month. eg. 2nd Wednesday in July
            'g' => '',      // Modified Julian day. This is different from the conventional Julian day number in two regards.
            'E' => 'D',     // day of week written in short form eg. Sun
            'EE' => 'D',
            'EEE' => 'D',
            'EEEE' => 'DD', // day of week fully written eg. Sunday
            'EEEEE' => '',
            'EEEEEE' => '',
            'e' => '',      // ISO-8601 numeric representation of the day of the week 1=Mon to 7=Sun
            'ee' => '',     // php 'w' 0=Sun to 6=Sat isn't supported by ICU -> 'w' means week number of year
            'eee' => 'D',
            'eeee' => '',
            'eeeee' => '',
            'eeeeee' => '',
            'c' => '',      // ISO-8601 numeric representation of the day of the week 1=Mon to 7=Sun
            'cc' => '',     // php 'w' 0=Sun to 6=Sat isn't supported by ICU -> 'w' means week number of year
            'ccc' => 'D',
            'cccc' => 'DD',
            'ccccc' => '',
            'cccccc' => '',
            'a' => '',      // am/pm marker
            'h' => '',      // 12-hour format of an hour without leading zeros 1 to 12h
            'hh' => '',     // 12-hour format of an hour with leading zeros, 01 to 12 h
            'H' => '',      // 24-hour format of an hour without leading zeros 0 to 23h
            'HH' => '',     // 24-hour format of an hour with leading zeros, 00 to 23 h
            'k' => '',      // hour in day (1~24)
            'kk' => '',     // hour in day (1~24)
            'K' => '',      // hour in am/pm (0~11)
            'KK' => '',     // hour in am/pm (0~11)
            'm' => '',      // Minutes without leading zeros, not supported by php but we fallback
            'mm' => '',     // Minutes with leading zeros
            's' => '',      // Seconds, without leading zeros, not supported by php but we fallback
            'ss' => '',     // Seconds, with leading zeros
            'S' => '',      // fractional second
            'SS' => '',     // fractional second
            'SSS' => '',    // fractional second
            'SSSS' => '',   // fractional second
            'A' => '',      // milliseconds in day
            'z' => '',      // Timezone abbreviation
            'zz' => '',     // Timezone abbreviation
            'zzz' => '',    // Timezone abbreviation
            'zzzz' => '',   // Timezone full name, not supported by php but we fallback
            'Z' => '',      // Difference to Greenwich time (GMT) in hours
            'ZZ' => '',     // Difference to Greenwich time (GMT) in hours
            'ZZZ' => '',    // Difference to Greenwich time (GMT) in hours
            'ZZZZ' => '',   // Time Zone: long localized GMT (=OOOO) e.g. GMT-08:00
            'ZZZZZ' => '',  // Time Zone: ISO8601 extended hms? (=XXXXX)
            'O' => '',      // Time Zone: short localized GMT e.g. GMT-8
            'OOOO' => '',   // Time Zone: long localized GMT (=ZZZZ) e.g. GMT-08:00
            'v' => '',      // Time Zone: generic non-location (falls back first to VVVV and then to OOOO) using the ICU defined fallback here
            'vvvv' => '',   // Time Zone: generic non-location (falls back first to VVVV and then to OOOO) using the ICU defined fallback here
            'V' => '',      // Time Zone: short time zone ID
            'VV' => '',     // Time Zone: long time zone ID
            'VVV' => '',    // Time Zone: time zone exemplar city
            'VVVV' => '',   // Time Zone: generic location (falls back to OOOO) using the ICU defined fallback here
            'X' => '',      // Time Zone: ISO8601 basic hm?, with Z for 0, e.g. -08, +0530, Z
            'XX' => '',     // Time Zone: ISO8601 basic hm, with Z, e.g. -0800, Z
            'XXX' => '',    // Time Zone: ISO8601 extended hm, with Z, e.g. -08:00, Z
            'XXXX' => '',   // Time Zone: ISO8601 basic hms?, with Z, e.g. -0800, -075258, Z
            'XXXXX' => '',  // Time Zone: ISO8601 extended hms?, with Z, e.g. -08:00, -07:52:58, Z
            'x' => '',      // Time Zone: ISO8601 basic hm?, without Z for 0, e.g. -08, +0530
            'xx' => '',     // Time Zone: ISO8601 basic hm, without Z, e.g. -0800
            'xxx' => '',    // Time Zone: ISO8601 extended hm, without Z, e.g. -08:00
            'xxxx' => '',   // Time Zone: ISO8601 basic hms?, without Z, e.g. -0800, -075258
            'xxxxx' => '',  // Time Zone: ISO8601 extended hms?, without Z, e.g. -08:00, -07:52:58
        ]));
    }

    /**
     * Converts a date format pattern from [php date() function format][] to [jQuery UI date format][].
     *
     * The conversion is limited to date patterns that do not use escaped characters.
     * Patterns like `jS \o\f F Y` which will result in a date like `1st of December 2014` may not be converted correctly
     * because of the use of escaped characters.
     *
     * Pattern constructs that are not supported by the jQuery UI format will be removed.
     *
     * [php date() function format]: https://www.php.net/manual/en/function.date.php
     * [jQuery UI date format]: https://api.jqueryui.com/datepicker/#utility-formatDate
     *
     * @param string $pattern date format pattern in php date()-function format.
     * @return string The converted date format pattern.
     */
    public static function convertDatePhpToJui($pattern)
    {
        // https://www.php.net/manual/en/function.date.php
        return strtr($pattern, [
            // Day
            'd' => 'dd',    // Day of the month, 2 digits with leading zeros 	01 to 31
            'D' => 'D',     // A textual representation of a day, three letters 	Mon through Sun
            'j' => 'd',     // Day of the month without leading zeros 	1 to 31
            'l' => 'DD',    // A full textual representation of the day of the week 	Sunday through Saturday
            'N' => '',      // ISO-8601 numeric representation of the day of the week, 1 (for Monday) through 7 (for Sunday)
            'S' => '',      // English ordinal suffix for the day of the month, 2 characters 	st, nd, rd or th. Works well with j
            'w' => '',      // Numeric representation of the day of the week 	0 (for Sunday) through 6 (for Saturday)
            'z' => 'o',     // The day of the year (starting from 0) 	0 through 365
            // Week
            'W' => '',      // ISO-8601 week number of year, weeks starting on Monday (added in PHP 4.1.0) 	Example: 42 (the 42nd week in the year)
            // Month
            'F' => 'MM',    // A full textual representation of a month, January through December
            'm' => 'mm',    // Numeric representation of a month, with leading zeros 	01 through 12
            'M' => 'M',     // A short textual representation of a month, three letters 	Jan through Dec
            'n' => 'm',     // Numeric representation of a month, without leading zeros 	1 through 12
            't' => '',      // Number of days in the given month 	28 through 31
            // Year
            'L' => '',      // Whether it's a leap year, 1 if it is a leap year, 0 otherwise.
            'o' => '',      // ISO-8601 year number. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead.
            'Y' => 'yy',    // A full numeric representation of a year, 4 digits 	Examples: 1999 or 2003
            'y' => 'y',     // A two digit representation of a year 	Examples: 99 or 03
            // Time
            'a' => '',      // Lowercase Ante meridiem and Post meridiem, am or pm
            'A' => '',      // Uppercase Ante meridiem and Post meridiem, AM or PM, not supported by ICU but we fallback to lowercase
            'B' => '',      // Swatch Internet time 	000 through 999
            'g' => '',      // 12-hour format of an hour without leading zeros 	1 through 12
            'G' => '',      // 24-hour format of an hour without leading zeros 0 to 23h
            'h' => '',      // 12-hour format of an hour with leading zeros, 01 to 12 h
            'H' => '',      // 24-hour format of an hour with leading zeros, 00 to 23 h
            'i' => '',      // Minutes with leading zeros 	00 to 59
            's' => '',      // Seconds, with leading zeros 	00 through 59
            'u' => '',      // Microseconds. Example: 654321
            // Timezone
            'e' => '',      // Timezone identifier. Examples: UTC, GMT, Atlantic/Azores
            'I' => '',      // Whether or not the date is in daylight saving time, 1 if Daylight Saving Time, 0 otherwise.
            'O' => '',      // Difference to Greenwich time (GMT) in hours, Example: +0200
            'P' => '',      // Difference to Greenwich time (GMT) with colon between hours and minutes, Example: +02:00
            'T' => '',      // Timezone abbreviation, Examples: EST, MDT ...
            'Z' => '',      // Timezone offset in seconds. The offset for timezones west of UTC is always negative, and for those east of UTC is always positive. -43200 through 50400
            // Full Date/Time
            'c' => 'yyyy-MM-dd', // ISO 8601 date, e.g. 2004-02-12T15:19:21+00:00, skipping the time here because it is not supported
            'r' => 'D, d M yy', // RFC 2822 formatted date, Example: Thu, 21 Dec 2000 16:01:07 +0200, skipping the time here because it is not supported
            'U' => '@',     // Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
        ]);
    }
}
