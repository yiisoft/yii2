<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use Yii;
use yii\helpers\FormatConverter;
use yii\i18n\Formatter;
use yiiunit\framework\i18n\IntlTestHelper;
use yiiunit\TestCase;

/**
 * @group helpers
 * @group i18n
 */
class FormatConverterTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        IntlTestHelper::setIntlStatus($this);

        $this->mockApplication([
            'timeZone' => 'UTC',
            'language' => 'ru-RU',
        ]);
    }

    protected function tearDown()
    {
        parent::tearDown();
        IntlTestHelper::resetIntlStatus();
    }

    public function testIntlIcuToPhpShortForm()
    {
        $this->assertEqualsAnyWhitespace('n/j/y', FormatConverter::convertDateIcuToPhp('short', 'date', 'en-US'));
        $this->assertEqualsAnyWhitespace('d.m.y', FormatConverter::convertDateIcuToPhp('short', 'date', 'de-DE'));
    }

    public function testIntlIcuToPhpShortFormDefaultLang()
    {
        Yii::$app->language = 'en';
        $this->assertEquals('n/j/y', FormatConverter::convertDateIcuToPhp('short', 'date'));
        Yii::$app->language = 'de';
        $this->assertEquals('d.m.y', FormatConverter::convertDateIcuToPhp('short', 'date'));
    }

    public function testIntlIcuToPhpShortFormTime()
    {
        $this->assertEqualsAnyWhitespace('g:i A', FormatConverter::convertDateIcuToPhp('short', 'time', 'en-US'));
        $this->assertEqualsAnyWhitespace('H:i', FormatConverter::convertDateIcuToPhp('short', 'time', 'de-DE'));
    }

    public function testIntlIcuToPhpShortFormDateTime()
    {
        $this->assertEqualsAnyWhitespace('n/j/y, g:i A', FormatConverter::convertDateIcuToPhp('short', 'datetime', 'en-US'));
        $this->assertEquals(
            PHP_VERSION_ID < 50600 ? 'd.m.y H:i' : 'd.m.y, H:i',
            FormatConverter::convertDateIcuToPhp('short', 'datetime', 'de-DE')
        );
    }

    public function testEscapedIcuToPhpMixedPatterns()
    {
        $this->assertEquals('l, F j, Y \\a\\t g:i:s A T', FormatConverter::convertDateIcuToPhp('EEEE, MMMM d, y \'at\' h:mm:ss a zzzz'));
        $this->assertEquals('\\o\\\'\\c\\l\\o\\c\\k', FormatConverter::convertDateIcuToPhp('\'o\'\'clock\''));
    }

    public function providerForICU2PHPPatterns()
    {
        return [
            'two single quotes produce one' => ["''", "\\'"],
            'era designator like (Anno Domini)' => ['G', ''],
            '4digit year of "Week of Year"' => ['Y', 'o'],
            '4digit year #1' => ['y', 'Y'],
            '4digit year #2' => ['yyyy', 'Y'],
            '2digit year number' => ['yy', 'y'],
            'extended year e.g. 4601' => ['u', ''],
            'cyclic year name, as in Chinese lunar calendar' => ['U', ''],
            'related Gregorian year e.g. 1996' => ['r', ''],
            'number of quarter' => ['Q', ''],
            'number of quarter "02"' => ['QQ', ''],
            'quarter "Q2"' => ['QQQ', ''],
            'quarter 2nd quarter' => ['QQQQ', ''],
            'number of quarter 2' => ['QQQQQ', ''],
            'number of Stand Alone quarter' => ['q', ''],
            'number of Stand Alone quarter "02"' => ['qq', ''],
            'Stand Alone quarter "Q2"' => ['qqq', ''],
            'Stand Alone quarter "2nd quarter"' => ['qqqq', ''],
            'number of Stand Alone quarter "2"' => ['qqqqq', ''],
            'Numeric representation of a month, without leading zeros' => ['M', 'n'],
            'Numeric representation of a month, with leading zeros' => ['MM', 'm'],
            'A short textual representation of a month, three letters' => ['MMM', 'M'],
            'A full textual representation of a month, such as January or March' => ['MMMM', 'F'],
            'MMMMM' => ['MMMMM', ''],
            'Stand alone month in year #1' => ['L', 'n'],
            'Stand alone month in year #2' => ['LL', 'm'],
            'Stand alone month in year #3' => ['LLL', 'M'],
            'Stand alone month in year #4' => ['LLLL', 'F'],
            'Stand alone month in year #5' => ['LLLLL', ''],
            'ISO-8601 week number of year #1' => ['w', 'W'],
            'ISO-8601 week number of year #2' => ['ww', 'W'],
            'week of the current month' => ['W', ''],
            'day without leading zeros' => ['d', 'j'],
            'day with leading zeros' => ['dd', 'd'],
            'day of the year 0 to 365' => ['D', 'z'],
            'Day of Week in Month. eg. 2nd Wednesday in July' => ['F', ''],
            'Modified Julian day. This is different from the conventional Julian day number in two regards.' => ['g', ''],
            'day of week written in short form eg. Sun' => ['E', 'D'],
            'EE' => ['EE', 'D'],
            'EEE' => ['EEE', 'D'],
            'day of week fully written eg. Sunday' => ['EEEE', 'l'],
            'EEEEE' => ['EEEEE', ''],
            'EEEEEE' => ['EEEEEE', ''],
            'ISO-8601 numeric representation of the day of the week 1=Mon to 7=Sun #1' => ['e', 'N'],
            'php "w" 0=Sun to 6=Sat isn`t supported by ICU -> "w" means week number of year #1' => ['ee', 'N'],
            'eee' => ['eee', 'D'],
            'eeee' => ['eeee', 'l'],
            'eeeee' => ['eeeee', ''],
            'eeeeee' => ['eeeeee', ''],
            'ISO-8601 numeric representation of the day of the week 1=Mon to 7=Sun #2' => ['c', 'N'],
            'php "w" 0=Sun to 6=Sat isn`t supported by ICU -> "w" means week number of year #2' => ['cc', 'N'],
            'ccc' => ['ccc', 'D'],
            'cccc' => ['cccc', 'l'],
            'ccccc' => ['ccccc', ''],
            'cccccc' => ['cccccc', ''],
            'AM/PM marker' => ['a', 'A'],
            '12-hour format of an hour without leading zeros 1 to 12h' => ['h', 'g'],
            '12-hour format of an hour with leading zeros, 01 to 12 h' => ['hh', 'h'],
            '24-hour format of an hour without leading zeros 0 to 23h' => ['H', 'G'],
            '24-hour format of an hour with leading zeros, 00 to 23 h' => ['HH', 'H'],
            'hour in day (1~24) #1' => ['k', ''],
            'hour in day (1~24) #2' => ['kk', ''],
            'hour in am/pm (0~11) #1' => ['K', ''],
            'hour in am/pm (0~11) #2' => ['KK', ''],
            'Minutes without leading zeros, not supported by php but we fallback' => ['m', 'i'],
            'Minutes with leading zeros' => ['mm', 'i'],
            'Seconds, without leading zeros, not supported by php but we fallback' => ['s', 's'],
            'Seconds, with leading zeros' => ['ss', 's'],
            'fractional second #1' => ['S', ''],
            'fractional second #2' => ['SS', ''],
            'fractional second #3' => ['SSS', ''],
            'fractional second #4' => ['SSSS', ''],
            'milliseconds in day' => ['A', ''],
            'Timezone abbreviation #1' => ['z', 'T'],
            'Timezone abbreviation #2' => ['zz', 'T'],
            'Timezone abbreviation #3' => ['zzz', 'T'],
            'Timezone full name, not supported by php but we fallback' => ['zzzz', 'T'],
            'Difference to Greenwich time (GMT) in hours #1' => ['Z', 'O'],
            'Difference to Greenwich time (GMT) in hours #2' => ['ZZ', 'O'],
            'Difference to Greenwich time (GMT) in hours #3' => ['ZZZ', 'O'],
            'Time Zone: long localized GMT (=OOOO) e.g. GMT-08:00' => ['ZZZZ', '\G\M\TP'],
            'TIme Zone: ISO8601 extended hms? (=XXXXX)' => ['ZZZZZ', ''],
            'Time Zone: short localized GMT e.g. GMT-8' => ['O', ''],
            'Time Zone: long localized GMT (=ZZZZ) e.g. GMT-08:00' => ['OOOO', '\G\M\TP'],
            'Time Zone: generic non-location (falls back first to VVVV and then to OOOO) using the ICU defined fallback here #1' => ['v', '\G\M\TP'],
            'Time Zone: generic non-location (falls back first to VVVV and then to OOOO) using the ICU defined fallback here #2' => ['vvvv', '\G\M\TP'],
            'Time Zone: short time zone ID' => ['V', ''],
            'Time Zone: long time zone ID' => ['VV', 'e'],
            'Time Zone: time zone exemplar city' => ['VVV', ''],
            'Time Zone: generic location (falls back to OOOO) using the ICU defined fallback here' => ['VVVV', '\G\M\TP'],
            'Time Zone: ISO8601 basic hm?, with Z for 0, e.g. -08, +0530, Z' => ['X', ''],
            'Time Zone: ISO8601 basic hm, with Z, e.g. -0800, Z' => ['XX', 'O, \Z'],
            'Time Zone: ISO8601 extended hm, with Z, e.g. -08:00, Z' => ['XXX', 'P, \Z'],
            'Time Zone: ISO8601 basic hms?, with Z, e.g. -0800, -075258, Z' => ['XXXX', ''],
            'Time Zone: ISO8601 extended hms?, with Z, e.g. -08:00, -07:52:58, Z' => ['XXXXX', ''],
            'Time Zone: ISO8601 basic hm?, without Z for 0, e.g. -08, +0530' => ['x', ''],
            'Time Zone: ISO8601 basic hm, without Z, e.g. -0800' => ['xx', 'O'],
            'Time Zone: ISO8601 extended hm, without Z, e.g. -08:00' => ['xxx', 'P'],
            'Time Zone: ISO8601 basic hms?, without Z, e.g. -0800, -075258' => ['xxxx', ''],
            'Time Zone: ISO8601 extended hms?, without Z, e.g. -08:00, -07:52:58' => ['xxxxx', ''],
        ];
    }

    /**
     * @dataProvider providerForICU2PHPPatterns
     */
    public function testEscapedIcuToPhpSinglePattern($pattern, $expected)
    {
        $this->assertEquals($expected, FormatConverter::convertDateIcuToPhp($pattern));
    }

    public function testEscapedIcuToJui()
    {
        $this->assertEquals('DD, MM d, yy \'at\' ', FormatConverter::convertDateIcuToJui('EEEE, MMMM d, y \'at\' zzzz'));
        $this->assertEquals('\'o\'\'clock\'', FormatConverter::convertDateIcuToJui('\'o\'\'clock\''));
    }

    public function testIntlIcuToJuiShortForm()
    {
        $this->assertEquals('m/d/y', FormatConverter::convertDateIcuToJui('short', 'date', 'en-US'));
        $this->assertEquals('dd.mm.y', FormatConverter::convertDateIcuToJui('short', 'date', 'de-DE'));
    }

    public function testIntlIcuToJuiShortFormDefaultLang()
    {
        Yii::$app->language = 'en';
        $this->assertEquals('m/d/y', FormatConverter::convertDateIcuToJui('short', 'date'));
        Yii::$app->language = 'de';
        $this->assertEquals('dd.mm.y', FormatConverter::convertDateIcuToJui('short', 'date'));
    }

    public function testIntlIcuToJuiShortFormTime()
    {
        $this->assertEqualsAnyWhitespace(': ', FormatConverter::convertDateIcuToJui('short', 'time', 'en-US'));
        $this->assertEqualsAnyWhitespace(':', FormatConverter::convertDateIcuToJui('short', 'time', 'de-DE'));
    }

    public function testIntlIcuToJuiShortFormDateTime()
    {
        $this->assertEqualsAnyWhitespace('m/d/y, : ', FormatConverter::convertDateIcuToJui('short', 'datetime', 'en-US'));
        $this->assertEquals(
            PHP_VERSION_ID < 50600 ? 'dd.mm.y :' : 'dd.mm.y, :',
            FormatConverter::convertDateIcuToJui('short', 'datetime', 'de-DE')
        );
    }

    public function providerForICU2JUIPatterns()
    {
        return [
            'era designator like (Anno Domini)' => ['G', ''],
            '4digit year of "Week of Year"' => ['Y', ''],
            '4digit year e.g. 2014 #1' => ['y', 'yy'],
            '4digit year e.g. 2014 #2' => ['yyyy', 'yy'],
            '2digit year number eg. 14' => ['yy', 'y'],
            'extended year e.g. 4601' => ['u', ''],
            'cyclic year name, as in Chinese lunar calendar' => ['U', ''],
            'related Gregorian year e.g. 1996' => ['r', ''],
            'number of quarter' => ['Q', ''],
            'number of quarter "02"' => ['QQ', ''],
            'quarter "Q2"' => ['QQQ', ''],
            'quarter "2nd quarter"' => ['QQQQ', ''],
            'number of quarter "2"' => ['QQQQQ', ''],
            'number of Stand Alone quarter' => ['q', ''],
            'number of Stand Alone quarter "02"' => ['qq', ''],
            'Stand Alone quarter "Q2"' => ['qqq', ''],
            'Stand Alone quarter "2nd quarter"' => ['qqqq', ''],
            'number of Stand Alone quarter "2"' => ['qqqqq', ''],
            'Numeric representation of a month, without leading zeros' => ['M', 'm'],
            'Numeric representation of a month, with leading zeros' => ['MM', 'mm'],
            'A short textual representation of a month, three letters' => ['MMM', 'M'],
            'A full textual representation of a month, such as January or March' => ['MMMM', 'MM'],
            'MMMMM' => ['MMMMM', ''],
            'Stand alone month in year #1' => ['L', 'm'],
            'Stand alone month in year #2' => ['LL', 'mm'],
            'Stand alone month in year #3' => ['LLL', 'M'],
            'Stand alone month in year #4' => ['LLLL', 'MM'],
            'Stand alone month in year #5' => ['LLLLL', ''],
            'ISO-8601 week number of year #1' => ['w', ''],
            'ISO-8601 week number of year #2' => ['ww', ''],
            'week of the current month' => ['W', ''],
            'day without leading zeros' => ['d', 'd'],
            'day with leading zeros' => ['dd', 'dd'],
            'day of the year 0 to 365' => ['D', 'o'],
            'Day of Week in Month. eg. 2nd Wednesday in July' => ['F', ''],
            'Modified Julian day. This is different from the conventional Julian day number in two regards.' => ['g', ''],
            'day of week written in short form eg. Sun' => ['E', 'D'],
            'EE' => ['EE', 'D'],
            'EEE' => ['EEE', 'D'],
            'day of week fully written eg. Sunday' => ['EEEE', 'DD'],
            'EEEEE' => ['EEEEE', ''],
            'EEEEEE' => ['EEEEEE', ''],
            'ISO-8601 numeric representation of the day of the week 1=Mon to 7=Sun #1' => ['e', ''],
            'php "w" 0=Sun to 6=Sat isn`t supported by ICU -> "w" means week number of year #1' => ['ee', ''],
            'eee' => ['eee', 'D'],
            'eeee' => ['eeee', ''],
            'eeeee' => ['eeeee', ''],
            'eeeeee' => ['eeeeee', ''],
            'ISO-8601 numeric representation of the day of the week 1=Mon to 7=Sun #2' => ['c', ''],
            'php "w" 0=Sun to 6=Sat isn`t supported by ICU -> "w" means week number of year #2' => ['cc', ''],
            'ccc' => ['ccc', 'D'],
            'cccc' => ['cccc', 'DD'],
            'ccccc' => ['ccccc', ''],
            'cccccc' => ['cccccc', ''],
            'am/pm marker' => ['a', ''],
            '12-hour format of an hour without leading zeros 1 to 12h' => ['h', ''],
            '12-hour format of an hour with leading zeros, 01 to 12 h' => ['hh', ''],
            '24-hour format of an hour without leading zeros 0 to 23h' => ['H', ''],
            '24-hour format of an hour with leading zeros, 00 to 23 h' => ['HH', ''],
            'hour in day (1~24) #1' => ['k', ''],
            'hour in day (1~24) #2' => ['kk', ''],
            'hour in am/pm (0~11) #1' => ['K', ''],
            'hour in am/pm (0~11) #2' => ['KK', ''],
            'Minutes without leading zeros, not supported by php but we fallback' => ['m', ''],
            'Minutes with leading zeros' => ['mm', ''],
            'Seconds, without leading zeros, not supported by php but we fallback' => ['s', ''],
            'Seconds, with leading zeros' => ['ss', ''],
            'fractional second #1' => ['S', ''],
            'fractional second #2' => ['SS', ''],
            'fractional second #3' => ['SSS', ''],
            'fractional second #4' => ['SSSS', ''],
            'milliseconds in day' => ['A', ''],
            'Timezone abbreviation #1' => ['z', ''],
            'Timezone abbreviation #2' => ['zz', ''],
            'Timezone abbreviation #3' => ['zzz', ''],
            'Timezone full name, not supported by php but we fallback' => ['zzzz', ''],
            'Difference to Greenwich time (GMT) in hours #1' => ['Z', ''],
            'Difference to Greenwich time (GMT) in hours #2' => ['ZZ', ''],
            'Difference to Greenwich time (GMT) in hours #3' => ['ZZZ', ''],
            'Time Zone: long localized GMT (=OOOO) e.g. GMT-08:00' => ['ZZZZ', ''],
            'Time Zone: ISO8601 extended hms? (=XXXXX)' => ['ZZZZZ', ''],
            'Time Zone: short localized GMT e.g. GMT-8' => ['O', ''],
            'Time Zone: long localized GMT (=ZZZZ) e.g. GMT-08:00' => ['OOOO', ''],
            'Time Zone: generic non-location (falls back first to VVVV and then to OOOO) using the ICU defined fallback here #1' => ['v', ''],
            'Time Zone: generic non-location (falls back first to VVVV and then to OOOO) using the ICU defined fallback here #2' => ['vvvv', ''],
            'Time Zone: short time zone ID' => ['V', ''],
            'Time Zone: long time zone ID' => ['VV', ''],
            'Time Zone: time zone exemplar city' => ['VVV', ''],
            'Time Zone: generic location (falls back to OOOO) using the ICU defined fallback here' => ['VVVV', ''],
            'Time Zone: ISO8601 basic hm?, with Z for 0, e.g. -08, +0530, Z' => ['X', ''],
            'Time Zone: ISO8601 basic hm, with Z, e.g. -0800, Z' => ['XX', ''],
            'Time Zone: ISO8601 extended hm, with Z, e.g. -08:00, Z' => ['XXX', ''],
            'Time Zone: ISO8601 basic hms?, with Z, e.g. -0800, -075258, Z' => ['XXXX', ''],
            'Time Zone: ISO8601 extended hms?, with Z, e.g. -08:00, -07:52:58, Z' => ['XXXXX', ''],
            'Time Zone: ISO8601 basic hm?, without Z for 0, e.g. -08, +0530' => ['x', ''],
            'Time Zone: ISO8601 basic hm, without Z, e.g. -0800' => ['xx', ''],
            'Time Zone: ISO8601 extended hm, without Z, e.g. -08:00' => ['xxx', ''],
            'Time Zone: ISO8601 basic hms?, without Z, e.g. -0800, -075258' => ['xxxx', ''],
            'Time Zone: ISO8601 extended hms?, without Z, e.g. -08:00, -07:52:58' => ['xxxxx', ''],
        ];
    }

    /**
     * @dataProvider providerForICU2JUIPatterns
     */
    public function testEscapedIcuToJuiSinglePattern($pattern, $expected)
    {
        $this->assertEquals($expected, FormatConverter::convertDateIcuToJui($pattern));
    }

    public function testIntlOneDigitIcu()
    {
        $formatter = new Formatter(['locale' => 'en-US']);
        $this->assertEquals('24.8.2014', $formatter->asDate('2014-8-24', 'php:d.n.Y'));
        $this->assertEquals('24.8.2014', $formatter->asDate('2014-8-24', 'd.M.yyyy'));
        $this->assertEquals('24.8.2014', $formatter->asDate('2014-8-24', 'd.L.yyyy'));
    }

    public function testOneDigitIcu()
    {
        $formatter = new Formatter(['locale' => 'en-US']);
        $this->assertEquals('24.8.2014', $formatter->asDate('2014-8-24', 'php:d.n.Y'));
        $this->assertEquals('24.8.2014', $formatter->asDate('2014-8-24', 'd.M.yyyy'));
        $this->assertEquals('24.8.2014', $formatter->asDate('2014-8-24', 'd.L.yyyy'));
    }

    public function testIntlUtf8Ru()
    {
        $this->assertEquals('d M Y \г.', FormatConverter::convertDateIcuToPhp("dd MMM y 'г'.", 'date', 'ru-RU'));
        $this->assertEquals("dd M yy 'г'.", FormatConverter::convertDateIcuToJui("dd MMM y 'г'.", 'date', 'ru-RU'));

        $formatter = new Formatter(['locale' => 'ru-RU']);
        // There is a dot after month name in updated ICU data and no dot in old data. Both are acceptable.
        // See https://github.com/yiisoft/yii2/issues/9906
        $this->assertRegExp('/24 авг\.? 2014 г\./', $formatter->asDate('2014-8-24', "dd MMM y 'г'."));
    }

    public function testPhpToICUMixedPatterns()
    {
        $expected = "yyyy-MM-dd'T'HH:mm:ssxxx";
        $actual = FormatConverter::convertDatePhpToIcu('Y-m-d\TH:i:sP');
        $this->assertEquals($expected, $actual);

        $expected = "yyyy-MM-dd'Yii'HH:mm:ssxxx";
        $actual = FormatConverter::convertDatePhpToIcu('Y-m-d\Y\i\iH:i:sP');
        $this->assertEquals($expected, $actual);

        $expected = "yyyy-MM-dd'Yii'HH:mm:ssxxx''''";
        $actual = FormatConverter::convertDatePhpToIcu("Y-m-d\Y\i\iH:i:sP''");
        $this->assertEquals($expected, $actual);

        $expected = "yyyy-MM-dd'Yii'\HH:mm:ssxxx''''";
        $actual = FormatConverter::convertDatePhpToIcu("Y-m-d\Y\i\i\\\\H:i:sP''");
        $this->assertEquals($expected, $actual);

        $expected = "'dDjlNSwZWFmMntLoYyaBghHisueIOPTZcru'";
        $actual = FormatConverter::convertDatePhpToIcu('\d\D\j\l\N\S\w\Z\W\F\m\M\n\t\L\o\Y\y\a\B\g\h\H\i\s\u\e\I\O\P\T\Z\c\r\u');
        $this->assertEquals($expected, $actual);

        $expected = "yyyy-MM-dd'T'HH:mm:ssxxx";
        $actual = FormatConverter::convertDatePhpToIcu('c');
        $this->assertEquals($expected, $actual);
    }

    public function providerForPHP2ICUPatterns()
    {
        return [
            'single \' should be encoded as \'\', which internally should be encoded as \'\'\'\'' => ["'", "''"],
            '\d' => ['\d', "'d'"],
            'Day of the month, 2 digits with leading zeros 01 to 31' => ['d', 'dd'],
            '\D' => ['\D', "'D'"],
            'A textual representation of a day, three letters Mon through Sun' => ['D', 'eee'],
            '\j' => ['\j', "'j'"],
            'Day of the month without leading zeros 1 to 31' => ['j', 'd'],
            '\l' => ['\l', "'l'"],
            'A full textual representation of the day of the week Sunday through Saturday' => ['l', 'eeee'],
            '\N' => ['\N', "'N'"],
            'ISO-8601 numeric representation of the day of the week, 1 (for Monday) through 7 (for Sunday)' => ['N', 'e'],
            '\S' => ['\S', "'S'"],
            'English ordinal suffix for the day of the month, 2 characters 	st, nd, rd or th. Works well with j' => ['S', ''],
            '\w' => ['\w', "'w'"],
            'Numeric representation of the day of the week 	0 (for Sunday) through 6 (for Saturday)' => ['w', ''],
            '\z' => ['\z', "'z'"],
            'The day of the year (starting from 0) 0 through 365' => ['z', 'D'],
            '\W' => ['\W', "'W'"],
            'ISO-8601 week number of year, weeks starting on Monday (added in PHP 4.1.0) Example: 42 (the 42nd week in the year)' => ['W', 'w'],
            '\F' => ['\F', "'F'"],
            'A full textual representation of a month, January through December' => ['F', 'MMMM'],
            '\m' => ['\m', "'m'"],
            'Numeric representation of a month, with leading zeros 01 through 12' => ['m', 'MM'],
            '\M' => ['\M', "'M'"],
            'A short textual representation of a month, three letters Jan through Dec' => ['M', 'MMM'],
            '\n' => ['\n', "'n'"],
            'Numeric representation of a month, without leading zeros 1 through 12, not supported by ICU but we fallback to "with leading zero"' => ['n', 'M'],
            '\t' => ['\t', "'t'"],
            'Number of days in the given month 28 through 31' => ['t', ''],
            '\L' => ['\L', "'L'"],
            'Whether it`s a leap year, 1 if it is a leap year, 0 otherwise.' => ['L', ''],
            '\o' => ['\o', "'o'"],
            'ISO-8601 year number. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead.' => ['o', 'Y'],
            '\Y' => ['\Y', "'Y'"],
            'A full numeric representation of a year, 4 digits Examples: 1999 or 2003' => ['Y', 'yyyy'],
            '\y' => ['\y', "'y'"],
            'A two digit representation of a year Examples: 99 or 03' => ['y', 'yy'],
            '\a' => ['\a', "'a'"],
            'Lowercase Ante meridiem and Post meridiem, am or pm' => ['a', 'a'],
            '\A' => ['\A', "'A'"],
            'Uppercase Ante meridiem and Post meridiem, AM or PM, not supported by ICU but we fallback to lowercase' => ['A', 'a'],
            '\B' => ['\B', "'B'"],
            '\A\B' => ['\A\B', "'AB'"],
            'Swatch Internet time 000 through 999' => ['B', ''],
            '\g' => ['\g', "'g'"],
            '12-hour format of an hour without leading zeros 1 through 12' => ['g', 'h'],
            '\G' => ['\G', "'G'"],
            '24-hour format of an hour without leading zeros 0 to 23h' => ['G', 'H'],
            '\h' => ['\h', "'h'"],
            '12-hour format of an hour with leading zeros, 01 to 12 h' => ['h', 'hh'],
            '\H' => ['\H', "'H'"],
            '24-hour format of an hour with leading zeros, 00 to 23 h' => ['H', 'HH'],
            '\i' => ['\i', "'i'"],
            'Minutes with leading zeros 00 to 59' => ['i', 'mm'],
            '\s' => ['\s', "'s'"],
            'Seconds, with leading zeros 00 through 59' => ['s', 'ss'],
            '\u' => ['\u', "'u'"],
            'Microseconds. Example: 654321' => ['u', ''],
            '\e' => ['\e', "'e'"],
            'Timezone identifier. Examples: UTC, GMT, Atlantic/Azores' => ['e', 'VV'],
            '\I' => ['\I', "'I'"],
            'Whether or not the date is in daylight saving time, 1 if Daylight Saving Time, 0 otherwise.' => ['I', ''],
            '\O' => ['\O', "'O'"],
            'Difference to Greenwich time (GMT) in hours, Example: +0200' => ['O', 'xx'],
            '\P' => ['\P', "'P'"],
            'Difference to Greenwich time (GMT) with colon between hours and minutes, Example: +02:00' => ['P', 'xxx'],
            '\T' => ['\T', "'T'"],
            'Timezone abbreviation, Examples: EST, MDT ...' => ['T', 'zzz'],
            '\Z' => ['\Z', "'Z'"],
            'Timezone offset in seconds. The offset for timezones west of UTC is always negative, and for those east of UTC is always positive. -43200 through 50400' => ['Z', ''],
            '\c' => ['\c', "'c'"],
            'ISO 8601 date, e.g. 2004-02-12T15:19:21+00:00' => ['c', "yyyy-MM-dd'T'HH:mm:ssxxx"],
            '\r' => ['\r', "'r'"],
            'RFC 2822 formatted date, Example: Thu, 21 Dec 2000 16:01:07 +0200' => ['r', 'eee, dd MMM yyyy HH:mm:ss xx'],
            '\U' => ['\U', "'U'"],
            'Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)' => ['U', ''],
            '\\' => ['\\\\', '\\'],
        ];
    }

    /**
     * @dataProvider providerForPHP2ICUPatterns
     */
    public function testPhpToICUSinglePattern($pattern, $expected)
    {
        $this->assertEquals($expected, FormatConverter::convertDatePhpToIcu($pattern));
    }

    public function testPhpFormatC()
    {
        $time = time();

        $formatter = new Formatter(['locale' => 'en-US']);
        $this->assertEquals(date('c', $time), $formatter->asDatetime($time, 'php:c'));

        date_default_timezone_set('Europe/Moscow');
        $formatter = new Formatter(['locale' => 'ru-RU', 'timeZone' => 'Europe/Moscow']);
        $this->assertEquals(date('c', $time), $formatter->asDatetime($time, 'php:c'));
    }

    public function testEscapedPhpToJuiMixedPatterns()
    {
        $this->assertEquals('dd-mm-yy', FormatConverter::convertDatePhpToJui('d-m-Y'));
    }

    public function providerForPHP2JUIPatterns()
    {
        return [
            'Day of the month, 2 digits with leading zeros 	01 to 31' => ['d', 'dd'],
            'A textual representation of a day, three letters 	Mon through Sun' => ['D', 'D'],
            'Day of the month without leading zeros 1 to 31' => ['j', 'd'],
            'A full textual representation of the day of the week Sunday through Saturday' => ['l', 'DD'],
            'ISO-8601 numeric representation of the day of the week, 1 (for Monday) through 7 (for Sunday)' => ['N', ''],
            'English ordinal suffix for the day of the month, 2 characters 	st, nd, rd or th. Works well with j' => ['S', ''],
            'Numeric representation of the day of the week 0 (for Sunday) through 6 (for Saturday)' => ['w', ''],
            'The day of the year (starting from 0) 0 through 365' => ['z', 'o'],
            'ISO-8601 week number of year, weeks starting on Monday (added in PHP 4.1.0) Example: 42 (the 42nd week in the year)' => ['W', ''],
            'A full textual representation of a month, January through December' => ['F', 'MM'],
            'Numeric representation of a month, with leading zeros 01 through 12' => ['m', 'mm'],
            'A short textual representation of a month, three letters Jan through Dec' => ['M', 'M'],
            'Numeric representation of a month, without leading zeros 1 through 12' => ['n', 'm'],
            'Number of days in the given month 	28 through 31' => ['t', ''],
            'Whether it`s a leap year, 1 if it is a leap year, 0 otherwise.' => ['L', ''],
            'ISO-8601 year number. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead.' => ['o', ''],
            'A full numeric representation of a year, 4 digits 	Examples: 1999 or 2003' => ['Y', 'yy'],
            'A two digit representation of a year Examples: 99 or 03' => ['y', 'y'],
            'Lowercase Ante meridiem and Post meridiem, am or pm' => ['a', ''],
            'Uppercase Ante meridiem and Post meridiem, AM or PM, not supported by ICU but we fallback to lowercase' => ['A', ''],
            'Swatch Internet time 000 through 999' => ['B', ''],
            '12-hour format of an hour without leading zeros 1 through 12' => ['g', ''],
            '24-hour format of an hour without leading zeros 0 to 23h' => ['G', ''],
            '12-hour format of an hour with leading zeros, 01 to 12 h' => ['h', ''],
            '24-hour format of an hour with leading zeros, 00 to 23 h' => ['H', ''],
            'Minutes with leading zeros 00 to 59' => ['i', ''],
            'Seconds, with leading zeros 00 through 59' => ['s', ''],
            'Microseconds. Example: 654321' => ['u', ''],
            'Timezone identifier. Examples: UTC, GMT, Atlantic/Azores' => ['e', ''],
            'Whether or not the date is in daylight saving time, 1 if Daylight Saving Time, 0 otherwise.' => ['I', ''],
            'Difference to Greenwich time (GMT) in hours, Example: +0200' => ['O', ''],
            'Difference to Greenwich time (GMT) with colon between hours and minutes, Example: +02:00' => ['P', ''],
            'Timezone abbreviation, Examples: EST, MDT ...' => ['T', ''],
            'Timezone offset in seconds. The offset for timezones west of UTC is always negative, and for those east of UTC is always positive. -43200 through 50400' => ['Z', ''],
            'ISO 8601 date, e.g. 2004-02-12T15:19:21+00:00, skipping the time here because it is not supported' => ['c', 'yyyy-MM-dd'],
            'RFC 2822 formatted date, Example: Thu, 21 Dec 2000 16:01:07 +0200, skipping the time here because it is not supported' => ['r', 'D, d M yy'],
            'Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)' => ['U', '@'],
        ];
    }

    /**
     * @dataProvider providerForPHP2JUIPatterns
     */
    public function testEscapedPhpToJuiSinglePattern($pattern, $expected)
    {
        $this->assertEquals($expected, FormatConverter::convertDatePhpToJui($pattern));
    }
}
