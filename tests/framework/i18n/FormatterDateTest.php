<?php

namespace yiiunit\framework\i18n;

use yii\i18n\Formatter;
use Yii;
use yiiunit\TestCase;
use DateTime;
use DateInterval;

/**
 * @group i18n
 */
class FormatterDateTest extends TestCase
{
    /**
     * @var Formatter
     */
    protected $formatter;

    protected function setUp()
    {
        parent::setUp();

        IntlTestHelper::setIntlStatus($this);

        $this->mockApplication([
            'timeZone' => 'UTC',
            'language' => 'ru-RU',
        ]);
        $this->formatter = new Formatter(['locale' => 'en-US']);
    }

    protected function tearDown()
    {
        parent::tearDown();
        IntlTestHelper::resetIntlStatus();
        $this->formatter = null;
    }


    public function testFormat()
    {
        $value = time();
        $this->assertSame(date('M j, Y', $value), $this->formatter->format($value, 'date'));
        $this->assertSame(date('M j, Y', $value), $this->formatter->format($value, 'DATE'));
        $this->assertSame(date('Y/m/d', $value), $this->formatter->format($value, ['date', 'php:Y/m/d']));
        $this->setExpectedException('\yii\base\InvalidParamException');
        $this->assertSame(date('Y-m-d', $value), $this->formatter->format($value, 'data'));
    }

    public function testIntlAsDate()
    {
        $this->testAsDate();
    }

    public function testAsDate()
    {
        $value = time();
        $this->assertSame(date('M j, Y', $value), $this->formatter->asDate($value));
        $this->assertSame(date('Y/m/d', $value), $this->formatter->asDate($value, 'php:Y/m/d'));
        $this->assertSame(date('m/d/Y', $value), $this->formatter->asDate($value, 'MM/dd/yyyy'));
        $this->assertSame(date('n/j/y', $value), $this->formatter->asDate($value, 'short'));
        $this->assertSame(date('F j, Y', $value), $this->formatter->asDate($value, 'long'));

        $value = new DateTime();
        $this->assertSame(date('M j, Y', $value->getTimestamp()), $this->formatter->asDate($value));
        $this->assertSame(date('Y/m/d', $value->getTimestamp()), $this->formatter->asDate($value, 'php:Y/m/d'));
        $this->assertSame(date('m/d/Y', $value->getTimestamp()), $this->formatter->asDate($value, 'MM/dd/yyyy'));
        $this->assertSame(date('n/j/y', $value->getTimestamp()), $this->formatter->asDate($value, 'short'));
        $this->assertSame(date('F j, Y', $value->getTimestamp()), $this->formatter->asDate($value, 'long'));

        if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            $value = new \DateTimeImmutable();
            $this->assertSame(date('M j, Y', $value->getTimestamp()), $this->formatter->asDate($value));
            $this->assertSame(date('Y/m/d', $value->getTimestamp()), $this->formatter->asDate($value, 'php:Y/m/d'));
            $this->assertSame(date('m/d/Y', $value->getTimestamp()), $this->formatter->asDate($value, 'MM/dd/yyyy'));
            $this->assertSame(date('n/j/y', $value->getTimestamp()), $this->formatter->asDate($value, 'short'));
            $this->assertSame(date('F j, Y', $value->getTimestamp()), $this->formatter->asDate($value, 'long'));
        }

        // empty input
        $this->assertSame('Jan 1, 1970', $this->formatter->asDate(''));
        $this->assertSame('Jan 1, 1970', $this->formatter->asDate(0));
        $this->assertSame('Jan 1, 1970', $this->formatter->asDate(false));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asDate(null));
    }

    public function testIntlAsTime()
    {
        $this->testAsTime();

        // empty input
        $this->formatter->locale = 'de-DE';
        $this->assertSame('00:00:00', $this->formatter->asTime(''));
        $this->assertSame('00:00:00', $this->formatter->asTime(0));
        $this->assertSame('00:00:00', $this->formatter->asTime(false));
    }

    public function testAsTime()
    {
        $value = time();
        $this->assertSame(date('g:i:s A', $value), $this->formatter->asTime($value));
        $this->assertSame(date('h:i:s A', $value), $this->formatter->asTime($value, 'php:h:i:s A'));

        $value = new DateTime();
        $this->assertSame(date('g:i:s A', $value->getTimestamp()), $this->formatter->asTime($value));
        $this->assertSame(date('h:i:s A', $value->getTimestamp()), $this->formatter->asTime($value, 'php:h:i:s A'));

        if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            $value = new \DateTimeImmutable();
            $this->assertSame(date('g:i:s A', $value->getTimestamp()), $this->formatter->asTime($value));
            $this->assertSame(date('h:i:s A', $value->getTimestamp()), $this->formatter->asTime($value, 'php:h:i:s A'));
        }

        // empty input
        $this->assertSame('12:00:00 AM', $this->formatter->asTime(''));
        $this->assertSame('12:00:00 AM', $this->formatter->asTime(0));
        $this->assertSame('12:00:00 AM', $this->formatter->asTime(false));
        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asTime(null));
    }

    public function testIntlAsDatetime()
    {
        $this->testAsDatetime();

        // empty input
        $this->formatter->locale = 'de-DE';
        $this->assertSame('01.01.1970 00:00:00', $this->formatter->asDatetime(''));
        $this->assertSame('01.01.1970 00:00:00', $this->formatter->asDatetime(0));
        $this->assertSame('01.01.1970 00:00:00', $this->formatter->asDatetime(false));
    }

    public function testAsDatetime()
    {
        $value = time();
        $this->assertSame(date('M j, Y g:i:s A', $value), $this->formatter->asDatetime($value));
        $this->assertSame(date('Y/m/d h:i:s A', $value), $this->formatter->asDatetime($value, 'php:Y/m/d h:i:s A'));

        $value = new DateTime();
        $this->assertSame(date('M j, Y g:i:s A', $value->getTimestamp()), $this->formatter->asDatetime($value));
        $this->assertSame(date('Y/m/d h:i:s A', $value->getTimestamp()), $this->formatter->asDatetime($value, 'php:Y/m/d h:i:s A'));

        if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            $value = new \DateTimeImmutable();
            $this->assertSame(date('M j, Y g:i:s A', $value->getTimestamp()), $this->formatter->asDatetime($value));
            $this->assertSame(date('Y/m/d h:i:s A', $value->getTimestamp()), $this->formatter->asDatetime($value, 'php:Y/m/d h:i:s A'));
        }

        // empty input
        $this->assertSame('Jan 1, 1970 12:00:00 AM', $this->formatter->asDatetime(''));
        $this->assertSame('Jan 1, 1970 12:00:00 AM', $this->formatter->asDatetime(0));
        $this->assertSame('Jan 1, 1970 12:00:00 AM', $this->formatter->asDatetime(false));
        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asDatetime(null));
    }

    public function testIntlAsTimestamp()
    {
        $this->testAsTimestamp();
    }

    public function testAsTimestamp()
    {
        $value = time();
        $this->assertSame("$value", $this->formatter->asTimestamp($value));
        $this->assertSame("$value", $this->formatter->asTimestamp((string) $value));

        $this->assertSame("$value", $this->formatter->asTimestamp(date('Y-m-d H:i:s', $value)));

        // empty input
        $this->assertSame("0", $this->formatter->asTimestamp(0));
        $this->assertSame("0", $this->formatter->asTimestamp(false));
        $this->assertSame("0", $this->formatter->asTimestamp(""));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asTimestamp(null));
    }

    public function testIntlDateRangeLow()
    {
        // intl does not support high date ranges on 32bit systems, the implementation uses a fallback to PHP formatter
        $this->testDateRangeLow();
    }

    /**
     * Test for dates before 1970
     * https://github.com/yiisoft/yii2/issues/3126
     */
    public function testDateRangeLow()
    {
        // http://en.wikipedia.org/wiki/Year_2038_problem
        $this->assertSame('13-12-1901', $this->formatter->asDate('1901-12-13', 'dd-MM-yyyy'));
        $this->assertSame('12-12-1901', $this->formatter->asDate('1901-12-12', 'dd-MM-yyyy'));

        $this->assertSame('12-08-1922', $this->formatter->asDate('1922-08-12', 'dd-MM-yyyy'));
        $this->assertSame('14-01-1732', $this->formatter->asDate('1732-01-14', 'dd-MM-yyyy'));
    }

    public function testIntlDateRangeHigh()
    {
        // intl does not support high date ranges on 32bit systems, the implementation uses a fallback to PHP formatter
        $this->testDateRangeHigh();
    }

    /**
     * Test for dates after 2038
     * https://github.com/yiisoft/yii2/issues/3126
     */
    public function testDateRangeHigh()
    {
        // http://en.wikipedia.org/wiki/Year_2038_problem
        $this->assertSame('19-01-2038', $this->formatter->asDate('2038-01-19', 'dd-MM-yyyy'));
        $this->assertSame('20-01-2038', $this->formatter->asDate('2038-01-20', 'dd-MM-yyyy'));

        $this->assertSame('17-12-2048', $this->formatter->asDate('2048-12-17', 'dd-MM-yyyy'));
        $this->assertSame('17-12-3048', $this->formatter->asDate('3048-12-17', 'dd-MM-yyyy'));
        $this->assertSame('31-12-9999', $this->formatter->asDate('9999-12-31', 'dd-MM-yyyy'));
    }

    private function buildDateSubIntervals($referenceDate, $intervals)
    {
        $date = new DateTime($referenceDate);
        foreach ($intervals as $interval) {
            $date->sub($interval);
        }
        return $date;
    }

    public function testIntlAsRelativeTime()
    {
        $this->testAsRelativeTime();
    }

    public function testAsRelativeTime()
    {
        $interval_1_second    = new DateInterval("PT1S");
        $interval_244_seconds = new DateInterval("PT244S");
        $interval_1_minute    = new DateInterval("PT1M");
        $interval_33_minutes  = new DateInterval("PT33M");
        $interval_1_hour      = new DateInterval("PT1H");
        $interval_6_hours     = new DateInterval("PT6H");
        $interval_1_day       = new DateInterval("P1D");
        $interval_89_days     = new DateInterval("P89D");
        $interval_1_month     = new DateInterval("P1M");
        $interval_5_months    = new DateInterval("P5M");
        $interval_1_year      = new DateInterval("P1Y");
        $interval_12_years    = new DateInterval("P12Y");

        // Pass a DateInterval
        $this->assertSame('a second ago', $this->formatter->asRelativeTime($interval_1_second));
        $this->assertSame('244 seconds ago', $this->formatter->asRelativeTime($interval_244_seconds));
        $this->assertSame('a minute ago', $this->formatter->asRelativeTime($interval_1_minute));
        $this->assertSame('33 minutes ago', $this->formatter->asRelativeTime($interval_33_minutes));
        $this->assertSame('an hour ago', $this->formatter->asRelativeTime($interval_1_hour));
        $this->assertSame('6 hours ago', $this->formatter->asRelativeTime($interval_6_hours));
        $this->assertSame('a day ago', $this->formatter->asRelativeTime($interval_1_day));
        $this->assertSame('89 days ago', $this->formatter->asRelativeTime($interval_89_days));
        $this->assertSame('a month ago', $this->formatter->asRelativeTime($interval_1_month));
        $this->assertSame('5 months ago', $this->formatter->asRelativeTime($interval_5_months));
        $this->assertSame('a year ago', $this->formatter->asRelativeTime($interval_1_year));
        $this->assertSame('12 years ago', $this->formatter->asRelativeTime($interval_12_years));

        // Pass a DateInterval string -> isn't possible
        //    $this->assertSame('a year ago', $this->formatter->asRelativeTime('2007-03-01T13:00:00Z/2008-05-11T15:30:00Z'));
        //    $this->assertSame('a year ago', $this->formatter->asRelativeTime('2007-03-01T13:00:00Z/P1Y2M10DT2H30M'));
        //    $this->assertSame('a year ago', $this->formatter->asRelativeTime('P1Y2M10DT2H30M/2008-05-11T15:30:00Z'));
        //    $this->assertSame('a year ago', $this->formatter->asRelativeTime('P1Y2M10DT2H30M'));
        //    $this->assertSame('94 months ago', $this->formatter->asRelativeTime('P94M'));

        // Force the reference time and pass a past DateTime
        $dateNow = new DateTime('2014-03-13');
        $this->assertSame('a second ago', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_1_second]), $dateNow));
        $this->assertSame('4 minutes ago', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_244_seconds]), $dateNow));
        $this->assertSame('a minute ago', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_1_minute]), $dateNow));
        $this->assertSame('33 minutes ago', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_33_minutes]), $dateNow));
        $this->assertSame('an hour ago', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_1_hour]), $dateNow));
        $this->assertSame('6 hours ago', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_6_hours]), $dateNow));
        $this->assertSame('a day ago', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_1_day]), $dateNow));
        $this->assertSame('2 months ago', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_89_days]), $dateNow));
        $this->assertSame('a month ago', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_1_month]), $dateNow));
        $this->assertSame('5 months ago', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_5_months]), $dateNow));
        $this->assertSame('a year ago', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_1_year]), $dateNow));
        $this->assertSame('12 years ago', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_12_years]), $dateNow));

        // Tricky 31-days month stuff
        // See: http://www.gnu.org/software/tar/manual/html_section/Relative-items-in-date-strings.html
        $dateNow = new DateTime('2014-03-31');
        $dateThen = new DateTime('2014-03-03');
        $this->assertSame('28 days ago', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-31', [$interval_1_month]), $dateNow));
        $this->assertSame('28 days ago', $this->formatter->asRelativeTime($dateThen, $dateNow));
        $dateThen = new DateTime('2014-02-28');
        $this->assertSame('a month ago', $this->formatter->asRelativeTime($dateThen, $dateNow));

        // Invert all the DateIntervals
        $interval_1_second->invert = true;
        $interval_244_seconds->invert = true;
        $interval_1_minute->invert = true;
        $interval_33_minutes->invert = true;
        $interval_1_hour->invert = true;
        $interval_6_hours->invert = true;
        $interval_1_day->invert = true;
        $interval_89_days->invert = true;
        $interval_1_month->invert = true;
        $interval_5_months->invert = true;
        $interval_1_year->invert = true;
        $interval_12_years->invert = true;

        // Pass a inverted DateInterval
        $this->assertSame('in a second', $this->formatter->asRelativeTime($interval_1_second));
        $this->assertSame('in 244 seconds', $this->formatter->asRelativeTime($interval_244_seconds));
        $this->assertSame('in a minute', $this->formatter->asRelativeTime($interval_1_minute));
        $this->assertSame('in 33 minutes', $this->formatter->asRelativeTime($interval_33_minutes));
        $this->assertSame('in an hour', $this->formatter->asRelativeTime($interval_1_hour));
        $this->assertSame('in 6 hours', $this->formatter->asRelativeTime($interval_6_hours));
        $this->assertSame('in a day', $this->formatter->asRelativeTime($interval_1_day));
        $this->assertSame('in 89 days', $this->formatter->asRelativeTime($interval_89_days));
        $this->assertSame('in a month', $this->formatter->asRelativeTime($interval_1_month));
        $this->assertSame('in 5 months', $this->formatter->asRelativeTime($interval_5_months));
        $this->assertSame('in a year', $this->formatter->asRelativeTime($interval_1_year));
        $this->assertSame('in 12 years', $this->formatter->asRelativeTime($interval_12_years));

        // Pass a inverted DateInterval string
        // $this->assertSame('in a year', $this->formatter->asRelativeTime('2008-05-11T15:30:00Z/2007-03-01T13:00:00Z'));

        // Force the reference time and pass a future DateTime
        $dateNow = new DateTime('2014-03-13');
        $this->assertSame('in a second', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_1_second]), $dateNow));
        $this->assertSame('in 4 minutes', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_244_seconds]), $dateNow));
        $this->assertSame('in a minute', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_1_minute]), $dateNow));
        $this->assertSame('in 33 minutes', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_33_minutes]), $dateNow));
        $this->assertSame('in an hour', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_1_hour]), $dateNow));
        $this->assertSame('in 6 hours', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_6_hours]), $dateNow));
        $this->assertSame('in a day', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_1_day]), $dateNow));
        $this->assertSame('in 2 months', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_89_days]), $dateNow));
        $this->assertSame('in a month', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_1_month]), $dateNow));
        $this->assertSame('in 5 months', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_5_months]), $dateNow));
        $this->assertSame('in a year', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_1_year]), $dateNow));
        $this->assertSame('in 12 years', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-13', [$interval_12_years]), $dateNow));

        // Tricky 31-days month stuff
        // See: http://www.gnu.org/software/tar/manual/html_section/Relative-items-in-date-strings.html
        $dateNow = new DateTime('2014-03-03');
        $dateThen = new DateTime('2014-03-31');
        $this->assertSame('in a month', $this->formatter->asRelativeTime($this->buildDateSubIntervals('2014-03-03', [$interval_1_month]), $dateNow));
        $this->assertSame('in 28 days', $this->formatter->asRelativeTime($dateThen, $dateNow));

        // just now
        $this->assertSame("just now", $this->formatter->asRelativeTime($t = time(), $t));
        $this->assertSame("just now", $this->formatter->asRelativeTime(0, 0));

        // empty input
        $this->assertSame("just now", $this->formatter->asRelativeTime(false, 0));
        $this->assertSame("just now", $this->formatter->asRelativeTime("", 0));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asRelativeTime(null));
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asRelativeTime(null, time()));
    }

    public function dateInputs()
    {
        return [
            ['2015-01-01 00:00:00', '2014-13-01 00:00:00'],
            [false, 'asdfg', 'yii\base\InvalidParamException'],
//            [(string)strtotime('now'), 'now'], // fails randomly
        ];
    }

    /**
     * @dataProvider dateInputs
     */
    public function testIntlDateInput($expected, $value, $expectedException = null)
    {
        $this->testDateInput($expected, $value, $expectedException);
    }

    /**
     * @dataProvider dateInputs
     */
    public function testDateInput($expected, $value, $expectedException = null)
    {
        if ($expectedException !== null) {
            $this->setExpectedException($expectedException);
        }
        $this->assertSame($expected, $this->formatter->asDate($value, 'yyyy-MM-dd HH:mm:ss'));
        $this->assertSame($expected, $this->formatter->asTime($value, 'yyyy-MM-dd HH:mm:ss'));
        $this->assertSame($expected, $this->formatter->asDatetime($value, 'yyyy-MM-dd HH:mm:ss'));
    }


    public function provideTimezones()
    {
        return [
            ['UTC'],
            ['Europe/Berlin'],
            ['America/Jamaica'],
            // these two are near the International Date Line on different sides
            ['Pacific/Kiritimati'],
            ['Pacific/Honolulu'],
        ];
    }

    /**
     * provide default timezones times input date value
     */
    public function provideTimesAndTz()
    {
        $utc = new \DateTimeZone('UTC');
        $berlin = new \DateTimeZone('Europe/Berlin');
        $result = [];
        foreach($this->provideTimezones() as $tz) {
            $result[] = [$tz[0], 1407674460,                          1388580060];
            $result[] = [$tz[0], '2014-08-10 12:41:00',               '2014-01-01 12:41:00'];
            $result[] = [$tz[0], '2014-08-10 12:41:00 UTC',           '2014-01-01 12:41:00 UTC'];
            $result[] = [$tz[0], '2014-08-10 14:41:00 Europe/Berlin', '2014-01-01 13:41:00 Europe/Berlin'];
            $result[] = [$tz[0], '2014-08-10 14:41:00 CEST',          '2014-01-01 13:41:00 CET'];
            $result[] = [$tz[0], '2014-08-10 14:41:00+0200',          '2014-01-01 13:41:00+0100'];
            $result[] = [$tz[0], '2014-08-10 14:41:00+02:00',         '2014-01-01 13:41:00+01:00'];
            $result[] = [$tz[0], '2014-08-10 14:41:00 +0200',         '2014-01-01 13:41:00 +0100'];
            $result[] = [$tz[0], '2014-08-10 14:41:00 +02:00',        '2014-01-01 13:41:00 +01:00'];
            $result[] = [$tz[0], '2014-08-10T14:41:00+02:00',         '2014-01-01T13:41:00+01:00']; // ISO 8601
            $result[] = [$tz[0], new DateTime('2014-08-10 12:41:00', $utc), new DateTime('2014-01-01 12:41:00', $utc)];
            $result[] = [$tz[0], new DateTime('2014-08-10 14:41:00', $berlin), new DateTime('2014-01-01 13:41:00', $berlin)];
            if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
                $result[] = [$tz[0], new \DateTimeImmutable('2014-08-10 12:41:00', $utc), new \DateTimeImmutable('2014-01-01 12:41:00', $utc)];
                $result[] = [$tz[0], new \DateTimeImmutable('2014-08-10 14:41:00', $berlin), new \DateTimeImmutable('2014-01-01 13:41:00', $berlin)];
            }
        }
        return $result;
    }

    /**
     * Test timezones with input date and time in other timezones
     * @dataProvider provideTimesAndTz
     */
    public function testIntlTimezoneInput($defaultTz, $inputTimeDst, $inputTimeNonDst)
    {
        $this->testTimezoneInput($defaultTz, $inputTimeDst, $inputTimeNonDst);
    }

    /**
     * Test timezones with input date and time in other timezones
     * @dataProvider provideTimesAndTz
     */
    public function testTimezoneInput($defaultTz, $inputTimeDst, $inputTimeNonDst)
    {
        date_default_timezone_set($defaultTz); // formatting has to be independent of the default timezone set by PHP
        $this->formatter->datetimeFormat = 'yyyy-MM-dd HH:mm:ss';
        $this->formatter->dateFormat = 'yyyy-MM-dd';
        $this->formatter->timeFormat = 'HH:mm:ss';

        // daylight saving time
        $this->formatter->timeZone = 'UTC';
        $this->assertSame('2014-08-10 12:41:00', $this->formatter->asDatetime($inputTimeDst));
        $this->assertSame('2014-08-10', $this->formatter->asDate($inputTimeDst));
        $this->assertSame('12:41:00', $this->formatter->asTime($inputTimeDst));
        $this->assertSame('1407674460', $this->formatter->asTimestamp($inputTimeDst));
        $this->formatter->timeZone = 'Europe/Berlin';
        $this->assertSame('2014-08-10 14:41:00', $this->formatter->asDatetime($inputTimeDst));
        $this->assertSame('2014-08-10', $this->formatter->asDate($inputTimeDst));
        $this->assertSame('14:41:00', $this->formatter->asTime($inputTimeDst));
        $this->assertSame('1407674460', $this->formatter->asTimestamp($inputTimeDst));

        // non daylight saving time
        $this->formatter->timeZone = 'UTC';
        $this->assertSame('2014-01-01 12:41:00', $this->formatter->asDatetime($inputTimeNonDst));
        $this->assertSame('2014-01-01', $this->formatter->asDate($inputTimeNonDst));
        $this->assertSame('12:41:00', $this->formatter->asTime($inputTimeNonDst));
        $this->assertSame('1388580060', $this->formatter->asTimestamp($inputTimeNonDst));
        $this->formatter->timeZone = 'Europe/Berlin';
        $this->assertSame('2014-01-01 13:41:00', $this->formatter->asDatetime($inputTimeNonDst));
        $this->assertSame('2014-01-01', $this->formatter->asDate($inputTimeNonDst));
        $this->assertSame('13:41:00', $this->formatter->asTime($inputTimeNonDst));
        $this->assertSame('1388580060', $this->formatter->asTimestamp($inputTimeNonDst));

        // tests for relative time
        if ($inputTimeDst !== 1407674460 && !is_object($inputTimeDst)) {
            $this->assertSame('3 hours ago', $this->formatter->asRelativeTime($inputTimeDst, $relativeTime = str_replace(['14:41', '12:41'], ['17:41', '15:41'], $inputTimeDst)));
            $this->assertSame('in 3 hours', $this->formatter->asRelativeTime($relativeTime, $inputTimeDst));
            $this->assertSame('3 hours ago', $this->formatter->asRelativeTime($inputTimeNonDst, $relativeTime = str_replace(['13:41', '12:41'], ['16:41', '15:41'], $inputTimeNonDst)));
            $this->assertSame('in 3 hours', $this->formatter->asRelativeTime($relativeTime, $inputTimeNonDst));
        }
    }


    /**
     * Test timezones with input date and time in other timezones
     */
    public function testTimezoneInputNonDefault()
    {
        $this->formatter->datetimeFormat = 'yyyy-MM-dd HH:mm:ss';
        $this->formatter->dateFormat = 'yyyy-MM-dd';
        $this->formatter->timeFormat = 'HH:mm:ss';

        $this->formatter->timeZone = 'UTC';
        $this->formatter->defaultTimeZone = 'UTC';
        $this->assertSame('2014-08-10 12:41:00', $this->formatter->asDatetime('2014-08-10 12:41:00'));
        $this->assertSame('2014-08-10', $this->formatter->asDate('2014-08-10 12:41:00'));
        $this->assertSame('12:41:00', $this->formatter->asTime('2014-08-10 12:41:00'));
        $this->assertSame('1407674460', $this->formatter->asTimestamp('2014-08-10 12:41:00'));

        $this->assertSame('2014-08-10 10:41:00', $this->formatter->asDatetime('2014-08-10 12:41:00 Europe/Berlin'));
        $this->assertSame('2014-08-10', $this->formatter->asDate('2014-08-10 12:41:00 Europe/Berlin'));
        $this->assertSame('10:41:00', $this->formatter->asTime('2014-08-10 12:41:00 Europe/Berlin'));
        $this->assertSame('1407674460', $this->formatter->asTimestamp('2014-08-10 14:41:00 Europe/Berlin'));

        $this->formatter->timeZone = 'Europe/Berlin';
        $this->formatter->defaultTimeZone = 'Europe/Berlin';
        $this->assertSame('2014-08-10 12:41:00', $this->formatter->asDatetime('2014-08-10 12:41:00'));
        $this->assertSame('2014-08-10', $this->formatter->asDate('2014-08-10 12:41:00'));
        $this->assertSame('12:41:00', $this->formatter->asTime('2014-08-10 12:41:00'));
        $this->assertSame('1407674460', $this->formatter->asTimestamp('2014-08-10 14:41:00'));

        $this->assertSame('2014-08-10 12:41:00', $this->formatter->asDatetime('2014-08-10 12:41:00 Europe/Berlin'));
        $this->assertSame('2014-08-10', $this->formatter->asDate('2014-08-10 12:41:00 Europe/Berlin'));
        $this->assertSame('12:41:00', $this->formatter->asTime('2014-08-10 12:41:00 Europe/Berlin'));
        $this->assertSame('1407674460', $this->formatter->asTimestamp('2014-08-10 14:41:00 Europe/Berlin'));

        $this->formatter->timeZone = 'UTC';
        $this->formatter->defaultTimeZone = 'Europe/Berlin';
        $this->assertSame('2014-08-10 10:41:00', $this->formatter->asDatetime('2014-08-10 12:41:00'));
        $this->assertSame('2014-08-10', $this->formatter->asDate('2014-08-10 12:41:00'));
        $this->assertSame('10:41:00', $this->formatter->asTime('2014-08-10 12:41:00'));
        $this->assertSame('1407674460', $this->formatter->asTimestamp('2014-08-10 14:41:00'));

        $this->assertSame('2014-08-10 12:41:00', $this->formatter->asDatetime('2014-08-10 12:41:00 UTC'));
        $this->assertSame('2014-08-10', $this->formatter->asDate('2014-08-10 12:41:00 UTC'));
        $this->assertSame('12:41:00', $this->formatter->asTime('2014-08-10 12:41:00 UTC'));
        $this->assertSame('1407674460', $this->formatter->asTimestamp('2014-08-10 12:41:00 UTC'));
    }


    public function testDateOnlyValues()
    {
        date_default_timezone_set('Pacific/Kiritimati');
        // timzones with exactly 24h difference, ensure this test does not fail on a certain time
        $this->formatter->defaultTimeZone = 'Pacific/Kiritimati'; // always UTC+14
        $this->formatter->timeZone = 'Pacific/Honolulu'; // always UTC-10

        // when timezone conversion is made on this date, it will result in 2014-07-31 to be returned.
        // ensure this does not happen on date only values
        $this->assertSame('2014-08-01', $this->formatter->asDate('2014-08-01', 'yyyy-MM-dd'));

        date_default_timezone_set('Pacific/Honolulu');
        $this->formatter->defaultTimeZone = 'Pacific/Honolulu'; // always UTC-10
        $this->formatter->timeZone = 'Pacific/Kiritimati'; // always UTC+14
        $this->assertSame('2014-08-01', $this->formatter->asDate('2014-08-01', 'yyyy-MM-dd'));
    }

    /**
     * https://github.com/yiisoft/yii2/issues/6263
     *
     * it is a PHP bug: https://bugs.php.net/bug.php?id=45543
     * Fixed in this commit: https://github.com/php/php-src/commit/22dba2f5f3211efe6c3b9bb24734c811ca64c68c#diff-7b738accc3d60f74c259da18588ddc5dL2996
     * Fixed in PHP >5.4.26 and >5.5.10. http://3v4l.org/mlZX7
     *
     * @dataProvider provideTimezones
     */
    public function testIssue6263($dtz)
    {
        $this->formatter->defaultTimeZone = $dtz;

        $this->formatter->timeZone = 'UTC';
        $this->assertEquals('24.11.2014 11:48:53', $this->formatter->format(1416829733, ['date', 'php:d.m.Y H:i:s']));
        $this->formatter->timeZone = 'Europe/Berlin';
        $this->assertEquals('24.11.2014 12:48:53', $this->formatter->format(1416829733, ['date', 'php:d.m.Y H:i:s']));

        $this->assertFalse(DateTime::createFromFormat('Y-m-d', 1416829733));
        $this->assertFalse(DateTime::createFromFormat('Y-m-d', '2014-05-08 12:48:53'));
        $this->assertFalse(DateTime::createFromFormat('Y-m-d H:i:s', 1416829733));
        $this->assertFalse(DateTime::createFromFormat('Y-m-d H:i:s', '2014-05-08'));
    }

    public function testIntlInputFractionSeconds()
    {
        $this->testInputFractionSeconds();
    }

    public function testInputFractionSeconds()
    {
        $this->formatter->defaultTimeZone = 'UTC';

        $timeStamp = '2015-04-28 10:06:15.000000';
        $this->formatter->timeZone = 'UTC';
        $this->assertEquals('2015-04-28 10:06:15+0000', $this->formatter->asDateTime($timeStamp, 'yyyy-MM-dd HH:mm:ssZ'));
        $this->formatter->timeZone = 'Europe/Berlin';
        $this->assertEquals('2015-04-28 12:06:15+0200', $this->formatter->asDateTime($timeStamp, 'yyyy-MM-dd HH:mm:ssZ'));

        $timeStamp = '2015-04-28 10:06:15';
        $this->formatter->timeZone = 'UTC';
        $this->assertEquals('2015-04-28 10:06:15+0000', $this->formatter->asDateTime($timeStamp, 'yyyy-MM-dd HH:mm:ssZ'));
        $this->formatter->timeZone = 'Europe/Berlin';
        $this->assertEquals('2015-04-28 12:06:15+0200', $this->formatter->asDateTime($timeStamp, 'yyyy-MM-dd HH:mm:ssZ'));
    }


    public function testInputUnixTimestamp()
    {
        $this->formatter->defaultTimeZone = 'UTC';
        $timeStamp = 1431907200;
        $this->formatter->timeZone = 'UTC';
        $this->assertEquals('2015-05-18 00:00:00+0000', $this->formatter->asDateTime($timeStamp, 'yyyy-MM-dd HH:mm:ssZ'));
        $this->formatter->timeZone = 'Europe/Berlin';
        $this->assertEquals('2015-05-18 02:00:00+0200', $this->formatter->asDateTime($timeStamp, 'yyyy-MM-dd HH:mm:ssZ'));

        $this->formatter->defaultTimeZone = 'Europe/Berlin';
        $timeStamp = 1431907200;
        $this->formatter->timeZone = 'UTC';
        $this->assertEquals('2015-05-18 00:00:00+0000', $this->formatter->asDateTime($timeStamp, 'yyyy-MM-dd HH:mm:ssZ'));
        $this->formatter->timeZone = 'Europe/Berlin';
        $this->assertEquals('2015-05-18 02:00:00+0200', $this->formatter->asDateTime($timeStamp, 'yyyy-MM-dd HH:mm:ssZ'));

        $this->formatter->defaultTimeZone = 'UTC';
        $timeStamp = -1431907200;
        $this->formatter->timeZone = 'UTC';
        $this->assertEquals('1924-08-17 00:00:00+0000', $this->formatter->asDateTime($timeStamp, 'yyyy-MM-dd HH:mm:ssZ'));
        $this->formatter->timeZone = 'Europe/Berlin';
        $this->assertEquals('1924-08-17 01:00:00+0100', $this->formatter->asDateTime($timeStamp, 'yyyy-MM-dd HH:mm:ssZ'));

        $this->formatter->defaultTimeZone = 'Europe/Berlin';
        $timeStamp = -1431907200;
        $this->formatter->timeZone = 'UTC';
        $this->assertEquals('1924-08-17 00:00:00+0000', $this->formatter->asDateTime($timeStamp, 'yyyy-MM-dd HH:mm:ssZ'));
        $this->formatter->timeZone = 'Europe/Berlin';
        $this->assertEquals('1924-08-17 01:00:00+0100', $this->formatter->asDateTime($timeStamp, 'yyyy-MM-dd HH:mm:ssZ'));

    }

}
