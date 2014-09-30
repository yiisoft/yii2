<?php

namespace yiiunit\framework\i18n;

use yii\i18n\Formatter;
use yiiunit\TestCase;
use DateTime;
use DateInterval;

/**
 * @group i18n
 */
class FormatterTest extends TestCase
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

    public function testLocale()
    {
        // locale is configured explicitly
        $f = new Formatter(['locale' => 'en-US']);
        $this->assertEquals('en-US', $f->locale);

        // if not, take from application
        $f = new Formatter();
        $this->assertEquals('ru-RU', $f->locale);
    }


    public function testAsRaw()
    {
        $value = '123';
        $this->assertSame($value, $this->formatter->asRaw($value));
        $value = 123;
        $this->assertSame($value, $this->formatter->asRaw($value));
        $value = '<>';
        $this->assertSame($value, $this->formatter->asRaw($value));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asRaw(null));
    }

    public function testAsText()
    {
        $value = '123';
        $this->assertSame($value, $this->formatter->asText($value));
        $value = 123;
        $this->assertSame("$value", $this->formatter->asText($value));
        $value = '<>';
        $this->assertSame('&lt;&gt;', $this->formatter->asText($value));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asText(null));
    }

    public function testAsNtext()
    {
        $value = '123';
        $this->assertSame($value, $this->formatter->asNtext($value));
        $value = 123;
        $this->assertSame("$value", $this->formatter->asNtext($value));
        $value = '<>';
        $this->assertSame('&lt;&gt;', $this->formatter->asNtext($value));
        $value = "123\n456";
        $this->assertSame("123<br />\n456", $this->formatter->asNtext($value));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asNtext(null));
    }

    public function testAsParagraphs()
    {
        $value = '123';
        $this->assertSame("<p>$value</p>", $this->formatter->asParagraphs($value));
        $value = 123;
        $this->assertSame("<p>$value</p>", $this->formatter->asParagraphs($value));
        $value = '<>';
        $this->assertSame('<p>&lt;&gt;</p>', $this->formatter->asParagraphs($value));
        $value = "123\n456";
        $this->assertSame("<p>123\n456</p>", $this->formatter->asParagraphs($value));
        $value = "123\n\n456";
        $this->assertSame("<p>123</p>\n<p>456</p>", $this->formatter->asParagraphs($value));
        $value = "123\n\n\n456";
        $this->assertSame("<p>123</p>\n<p>456</p>", $this->formatter->asParagraphs($value));
        $value = "123\r\n456";
        $this->assertSame("<p>123\r\n456</p>", $this->formatter->asParagraphs($value));
        $value = "123\r\n\r\n456";
        $this->assertSame("<p>123</p>\n<p>456</p>", $this->formatter->asParagraphs($value));
        $value = "123\r\n\r\n\r\n456";
        $this->assertSame("<p>123</p>\n<p>456</p>", $this->formatter->asParagraphs($value));
        $value = "123\r456";
        $this->assertSame("<p>123\r456</p>", $this->formatter->asParagraphs($value));
        $value = "123\r\r456";
        $this->assertSame("<p>123</p>\n<p>456</p>", $this->formatter->asParagraphs($value));
        $value = "123\r\r\r456";
        $this->assertSame("<p>123</p>\n<p>456</p>", $this->formatter->asParagraphs($value));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asParagraphs(null));
    }

    public function testAsHtml()
    {
        // todo: dependency on HtmlPurifier
    }

    public function testAsEmail()
    {
        $value = 'test@sample.com';
        $this->assertSame("<a href=\"mailto:$value\">$value</a>", $this->formatter->asEmail($value));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asEmail(null));
    }

    public function testAsUrl()
    {
        $value = 'http://www.yiiframework.com/';
        $this->assertSame("<a href=\"$value\">$value</a>", $this->formatter->asUrl($value));
        $value = 'https://www.yiiframework.com/';
        $this->assertSame("<a href=\"$value\">$value</a>", $this->formatter->asUrl($value));
        $value = 'www.yiiframework.com/';
        $this->assertSame("<a href=\"http://$value\">$value</a>", $this->formatter->asUrl($value));
        $value = 'https://www.yiiframework.com/?name=test&value=5"';
        $this->assertSame("<a href=\"https://www.yiiframework.com/?name=test&amp;value=5&quot;\">https://www.yiiframework.com/?name=test&amp;value=5&quot;</a>", $this->formatter->asUrl($value));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asUrl(null));
    }

    public function testAsImage()
    {
        $value = 'http://sample.com/img.jpg';
        $this->assertSame("<img src=\"$value\" alt=\"\">", $this->formatter->asImage($value));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asImage(null));
    }

    public function testAsBoolean()
    {
        $this->assertSame('Yes', $this->formatter->asBoolean(true));
        $this->assertSame('No', $this->formatter->asBoolean(false));
        $this->assertSame('Yes', $this->formatter->asBoolean("111"));
        $this->assertSame('No', $this->formatter->asBoolean(""));
        $this->assertSame('No', $this->formatter->asBoolean(0));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asBoolean(null));
    }


    // date format


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
        if (PHP_INT_SIZE == 4) { // 32bit systems
            $this->markTestSkipped('intl does not support high date ranges on 32bit systems.');
        }
        $this->testDateRangeLow();
    }

    /**
     * Test for dates before 1970
     * https://github.com/yiisoft/yii2/issues/3126
     */
    public function testDateRangeLow()
    {
        $this->assertSame('12-08-1922', $this->formatter->asDate('1922-08-12', 'dd-MM-yyyy'));
        $this->assertSame('14-01-1732', $this->formatter->asDate('1732-01-14', 'dd-MM-yyyy'));
    }

    public function testIntlDateRangeHigh()
    {
        if (PHP_INT_SIZE == 4) { // 32bit systems
            $this->markTestSkipped('intl does not support high date ranges on 32bit systems.');
        }
        $this->testDateRangeHigh();
    }

        /**
     * Test for dates after 2038
     * https://github.com/yiisoft/yii2/issues/3126
     */
    public function testDateRangeHigh()
    {
        $this->assertSame('17-12-2048', $this->formatter->asDate('2048-12-17', 'dd-MM-yyyy'));
        $this->assertSame('17-12-3048', $this->formatter->asDate('3048-12-17', 'dd-MM-yyyy'));
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
//            ['2015-01-01 00:00:00', '2014-13-01'], // TODO evals to current time on that date
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
        ];
    }

    /**
     * provide default timezones times input date value
     */
    public function provideTimesAndTz()
    {
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
        if ($inputTimeDst !== 1407674460) {
            $this->assertSame('3 hours ago', $this->formatter->asRelativeTime($inputTimeDst, $relativeTime = str_replace(['14:41', '12:41'], ['17:41', '15:41'], $inputTimeDst)));
            $this->assertSame('in 3 hours', $this->formatter->asRelativeTime($relativeTime, $inputTimeDst));
            $this->assertSame('3 hours ago', $this->formatter->asRelativeTime($inputTimeNonDst, $relativeTime = str_replace(['13:41', '12:41'], ['16:41', '15:41'], $inputTimeNonDst)));
            $this->assertSame('in 3 hours', $this->formatter->asRelativeTime($relativeTime, $inputTimeNonDst));
        }
    }


    // number format


    public function testIntlAsInteger()
    {
        $this->testAsInteger();
    }

    public function testAsInteger()
    {
        $this->assertSame("123", $this->formatter->asInteger(123));
        $this->assertSame("123", $this->formatter->asInteger(123.23));
        $this->assertSame("123", $this->formatter->asInteger(123.53));
        $this->assertSame("0", $this->formatter->asInteger(0));
        $this->assertSame("-123", $this->formatter->asInteger(-123.23));
        $this->assertSame("-123", $this->formatter->asInteger(-123.53));

        $this->assertSame("123,456", $this->formatter->asInteger(123456));
        $this->assertSame("123,456", $this->formatter->asInteger(123456.789));

        // empty input
        $this->assertSame("0", $this->formatter->asInteger(false));
        $this->assertSame("0", $this->formatter->asInteger(""));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asInteger(null));
    }

    /**
     * @expectedException \yii\base\InvalidParamException
     */
    public function testAsIntegerException()
    {
        $this->formatter->asInteger('a');
    }

    /**
     * @expectedException \yii\base\InvalidParamException
     */
    public function testAsIntegerException2()
    {
        $this->formatter->asInteger('-123abc');
    }

    public function testIntlAsDecimal()
    {
        $value = 123.12;
        $this->assertSame("123.12", $this->formatter->asDecimal($value, 2));
        $this->assertSame("123.1", $this->formatter->asDecimal($value, 1));
        $this->assertSame("123", $this->formatter->asDecimal($value, 0));

        $value = 123;
        $this->assertSame("123", $this->formatter->asDecimal($value));
        $this->assertSame("123.00", $this->formatter->asDecimal($value, 2));
        $this->formatter->decimalSeparator = ',';
        $this->formatter->thousandSeparator = '.';
        $value = 123.12;
        $this->assertSame("123,12", $this->formatter->asDecimal($value));
        $this->assertSame("123,1", $this->formatter->asDecimal($value, 1));
        $this->assertSame("123", $this->formatter->asDecimal($value, 0));
        $value = 123123.123;
        $this->assertSame("123.123", $this->formatter->asDecimal($value, 0));
        $this->assertSame("123.123,12", $this->formatter->asDecimal($value, 2));
        $this->formatter->thousandSeparator = '';
        $this->assertSame("123123,1", $this->formatter->asDecimal($value, 1));
        $this->formatter->thousandSeparator = ' ';
        $this->assertSame("12 31 23,1", $this->formatter->asDecimal($value, 1, [\NumberFormatter::GROUPING_SIZE => 2]));

        $value = 123123.123;
        $this->formatter->decimalSeparator = ',';
        $this->formatter->thousandSeparator = ' ';
        $this->assertSame("123 123", $this->formatter->asDecimal($value, 0));
        $this->assertSame("123 123,12", $this->formatter->asDecimal($value, 2));

        $this->formatter->decimalSeparator = null;
        $this->formatter->thousandSeparator = null;
        $value = '-123456.123';
        $this->assertSame("-123,456.123", $this->formatter->asDecimal($value));

        // empty input
        $this->assertSame("0", $this->formatter->asInteger(false));
        $this->assertSame("0", $this->formatter->asInteger(""));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asDecimal(null));
    }

    public function testAsDecimal()
    {
        $value = 123.12;
        $this->assertSame("123.12", $this->formatter->asDecimal($value));
        $this->assertSame("123.1", $this->formatter->asDecimal($value, 1));
        $this->assertSame("123", $this->formatter->asDecimal($value, 0));
        $value = 123;
        $this->assertSame("123.00", $this->formatter->asDecimal($value));
        $this->formatter->decimalSeparator = ',';
        $this->formatter->thousandSeparator = '.';
        $value = 123.12;
        $this->assertSame("123,12", $this->formatter->asDecimal($value));
        $this->assertSame("123,1", $this->formatter->asDecimal($value, 1));
        $this->assertSame("123", $this->formatter->asDecimal($value, 0));
        $value = 123123.123;
        $this->assertSame("123.123,12", $this->formatter->asDecimal($value));

        $value = 123123.123;
        $this->assertSame("123.123,12", $this->formatter->asDecimal($value));
        $this->assertSame("123.123,12", $this->formatter->asDecimal($value, 2));
        $this->formatter->decimalSeparator = ',';
        $this->formatter->thousandSeparator = ' ';
        $this->assertSame("123 123,12", $this->formatter->asDecimal($value));
        $this->assertSame("123 123,12", $this->formatter->asDecimal($value, 2));
        $this->formatter->thousandSeparator = '';
        $this->assertSame("123123,12", $this->formatter->asDecimal($value));
        $this->assertSame("123123,12", $this->formatter->asDecimal($value, 2));

        $this->formatter->decimalSeparator = null;
        $this->formatter->thousandSeparator = null;
        $value = '-123456.123';
        $this->assertSame("-123,456.123", $this->formatter->asDecimal($value, 3));

        // empty input
        $this->assertSame("0", $this->formatter->asInteger(false));
        $this->assertSame("0", $this->formatter->asInteger(""));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asDecimal(null));
    }

    public function testIntlAsPercent()
    {
        $this->testAsPercent();
    }

    public function testAsPercent()
    {
        $this->assertSame('12,300%', $this->formatter->asPercent(123));
        $this->assertSame('12,300%', $this->formatter->asPercent('123'));
        $this->assertSame("12%", $this->formatter->asPercent(0.1234));
        $this->assertSame("12%", $this->formatter->asPercent('0.1234'));
        $this->assertSame("-1%", $this->formatter->asPercent(-0.009343));
        $this->assertSame("-1%", $this->formatter->asPercent('-0.009343'));

        // empty input
        $this->assertSame("0", $this->formatter->asInteger(false));
        $this->assertSame("0", $this->formatter->asInteger(""));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asPercent(null));
    }

    public function testIntlAsCurrency()
    {
        $this->formatter->locale = 'en-US';
        $this->assertSame('$123.00', $this->formatter->asCurrency('123'));
        $this->assertSame('$123,456.00', $this->formatter->asCurrency('123456'));
        $this->assertSame('$0.00', $this->formatter->asCurrency('0'));

        $this->formatter->locale = 'en-US';
        $this->formatter->currencyCode = 'USD';
        $this->assertSame('$123.00', $this->formatter->asCurrency('123'));
        $this->assertSame('$123,456.00', $this->formatter->asCurrency('123456'));
        $this->assertSame('$0.00', $this->formatter->asCurrency('0'));
        // Starting from ICU 52.1, negative currency value will be formatted as -$123,456.12
        // see: http://source.icu-project.org/repos/icu/icu/tags/release-52-1/source/data/locales/en.txt
//		$value = '-123456.123';
//		$this->assertSame("($123,456.12)", $this->formatter->asCurrency($value));

        $this->formatter->locale = 'de-DE';
        $this->formatter->currencyCode = null;
        $this->assertSame('123,00 €', $this->formatter->asCurrency('123'));
        $this->formatter->currencyCode = 'USD';
        $this->assertSame('123,00 $', $this->formatter->asCurrency('123'));
        $this->formatter->currencyCode = 'EUR';
        $this->assertSame('123,00 €', $this->formatter->asCurrency('123'));

        // empty input
        $this->assertSame("0", $this->formatter->asInteger(false));
        $this->assertSame("0", $this->formatter->asInteger(""));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asCurrency(null));
    }

    /**
     * https://github.com/yiisoft/yii2/pull/5261
     */
    public function testIntlIssue5261()
    {
        $this->formatter->locale = 'en-US';
        $this->formatter->numberFormatterOptions = [
            \NumberFormatter::FRACTION_DIGITS => 0
        ];
        $this->formatter->numberFormatterTextOptions = [
            \NumberFormatter::CURRENCY_CODE => 'EUR'
        ];
        $this->assertSame('€100', $this->formatter->asCurrency(100, 'EUR'));
    }

    public function testAsCurrency()
    {
        $this->formatter->currencyCode = 'USD';
        $this->assertSame('USD 123.00', $this->formatter->asCurrency('123'));
        $this->assertSame('USD 0.00', $this->formatter->asCurrency('0'));
        $this->assertSame('USD -123.45', $this->formatter->asCurrency('-123.45'));
        $this->assertSame('USD -123.45', $this->formatter->asCurrency(-123.45));

        $this->formatter->currencyCode = 'EUR';
        $this->assertSame('EUR 123.00', $this->formatter->asCurrency('123'));
        $this->assertSame('EUR 0.00', $this->formatter->asCurrency('0'));
        $this->assertSame('EUR -123.45', $this->formatter->asCurrency('-123.45'));
        $this->assertSame('EUR -123.45', $this->formatter->asCurrency(-123.45));

        // empty input
        $this->assertSame("0", $this->formatter->asInteger(false));
        $this->assertSame("0", $this->formatter->asInteger(""));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asCurrency(null));
    }

    public function testIntlAsScientific()
    {
        $value = '123';
        $this->assertSame('1.23E2', $this->formatter->asScientific($value));
        $value = '123456';
        $this->assertSame("1.23456E5", $this->formatter->asScientific($value));
        $value = '-123456.123';
        $this->assertSame("-1.23456123E5", $this->formatter->asScientific($value));

        // empty input
        $this->assertSame("0", $this->formatter->asInteger(false));
        $this->assertSame("0", $this->formatter->asInteger(""));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asScientific(null));
    }

    public function testAsScientific()
    {
        $value = '123';
        $this->assertSame('1.23E+2', $this->formatter->asScientific($value, 2));
        $value = '123456';
        $this->assertSame("1.234560E+5", $this->formatter->asScientific($value));
        $value = '-123456.123';
        $this->assertSame("-1.234561E+5", $this->formatter->asScientific($value));

        // empty input
        $this->assertSame("0", $this->formatter->asInteger(false));
        $this->assertSame("0", $this->formatter->asInteger(""));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asScientific(null));
    }

    public function testIntlAsSpellout()
    {
        $this->assertSame('one hundred twenty-three', $this->formatter->asSpellout(123));

        $this->formatter->locale = 'de_DE';
        $this->assertSame('ein­hundert­drei­und­zwanzig', $this->formatter->asSpellout(123));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asSpellout(null));
    }

    public function testIntlAsOrdinal()
    {
        $this->assertSame('0th', $this->formatter->asOrdinal(0));
        $this->assertSame('1st', $this->formatter->asOrdinal(1));
        $this->assertSame('2nd', $this->formatter->asOrdinal(2));
        $this->assertSame('3rd', $this->formatter->asOrdinal(3));
        $this->assertSame('5th', $this->formatter->asOrdinal(5));

        $this->formatter->locale = 'de_DE';
        $this->assertSame('0.', $this->formatter->asOrdinal(0));
        $this->assertSame('1.', $this->formatter->asOrdinal(1));
        $this->assertSame('2.', $this->formatter->asOrdinal(2));
        $this->assertSame('3.', $this->formatter->asOrdinal(3));
        $this->assertSame('5.', $this->formatter->asOrdinal(5));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asOrdinal(null));
    }

    public function testIntlAsShortSize()
    {
        $this->formatter->numberFormatterOptions = [
            \NumberFormatter::MIN_FRACTION_DIGITS => 0,
            \NumberFormatter::MAX_FRACTION_DIGITS => 2,
        ];

        // tests for base 1000
        $this->formatter->sizeFormatBase = 1000;
        $this->assertSame("999 B", $this->formatter->asShortSize(999));
        $this->assertSame("999 B", $this->formatter->asShortSize('999'));
        $this->assertSame("1.05 MB", $this->formatter->asShortSize(1024 * 1024));
        $this->assertSame("1 KB", $this->formatter->asShortSize(1000));
        $this->assertSame("1.02 KB", $this->formatter->asShortSize(1023));
        $this->assertNotEquals("3 PB", $this->formatter->asShortSize(3 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000)); // this is 3 EB not 3 PB
        // tests for base 1024
        $this->formatter->sizeFormatBase = 1024;
        $this->assertSame("1 KiB", $this->formatter->asShortSize(1024));
        $this->assertSame("1 MiB", $this->formatter->asShortSize(1024 * 1024));
        // https://github.com/yiisoft/yii2/issues/4960
        $this->assertSame("1023 B", $this->formatter->asShortSize(1023));
        $this->assertSame("5 GiB", $this->formatter->asShortSize(5 * 1024 * 1024 * 1024));
        $this->assertNotEquals("5 PiB", $this->formatter->asShortSize(5 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024)); // this is 5 EiB not 5 PiB
        //$this->assertSame("1 YiB", $this->formatter->asShortSize(pow(2, 80)));
        $this->assertSame("2 GiB", $this->formatter->asShortSize(2147483647)); // round 1.999 up to 2
        $this->formatter->decimalSeparator = ',';
        $this->formatter->numberFormatterOptions = [];
        $this->assertSame("1,001 KiB", $this->formatter->asShortSize(1025, 3));

        // empty values
        $this->assertSame('0 B', $this->formatter->asShortSize(0));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asShortSize(null));
    }

    public function testAsShortSize()
    {
        // tests for base 1000
        $this->formatter->sizeFormatBase = 1000;
        $this->assertSame("999 B", $this->formatter->asShortSize(999));
        $this->assertSame("999 B", $this->formatter->asShortSize('999'));
        $this->assertSame("1.05 MB", $this->formatter->asShortSize(1024 * 1024));
        $this->assertSame("1.0486 MB", $this->formatter->asShortSize(1024 * 1024, 4));
        $this->assertSame("1.00 KB", $this->formatter->asShortSize(1000));
        $this->assertSame("1.02 KB", $this->formatter->asShortSize(1023));
        $this->assertNotEquals("3 PB", $this->formatter->asShortSize(3 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000)); // this is 3 EB not 3 PB
        // tests for base 1024
        $this->formatter->sizeFormatBase = 1024;
        $this->assertSame("1.00 KiB", $this->formatter->asShortSize(1024));
        $this->assertSame("1.00 MiB", $this->formatter->asShortSize(1024 * 1024));
        // https://github.com/yiisoft/yii2/issues/4960
        $this->assertSame("1023 B", $this->formatter->asShortSize(1023));
        $this->assertSame("5.00 GiB", $this->formatter->asShortSize(5 * 1024 * 1024 * 1024));
        $this->assertNotEquals("5.00 PiB", $this->formatter->asShortSize(5 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024)); // this is 5 EiB not 5 PiB
        //$this->assertSame("1 YiB", $this->formatter->asShortSize(pow(2, 80)));
        $this->assertSame("2.00 GiB", $this->formatter->asShortSize(2147483647)); // round 1.999 up to 2
        $this->formatter->decimalSeparator = ',';
        $this->assertSame("1,001 KiB", $this->formatter->asShortSize(1025, 3));

        // empty values
        $this->assertSame('0 B', $this->formatter->asShortSize(0));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asShortSize(null));
    }

    public function testIntlAsSize()
    {
        $this->formatter->numberFormatterOptions = [
            \NumberFormatter::MIN_FRACTION_DIGITS => 0,
            \NumberFormatter::MAX_FRACTION_DIGITS => 2,
        ];

        // tests for base 1000
        $this->formatter->sizeFormatBase = 1000;
        $this->assertSame("999 bytes", $this->formatter->asSize(999));
        $this->assertSame("999 bytes", $this->formatter->asSize('999'));
        $this->assertSame("1.05 megabytes", $this->formatter->asSize(1024 * 1024));
        $this->assertSame("1 kilobyte", $this->formatter->asSize(1000));
        $this->assertSame("1.02 kilobytes", $this->formatter->asSize(1023));
        $this->assertSame("3 gigabytes", $this->formatter->asSize(3 * 1000 * 1000 * 1000));
        $this->assertNotEquals("3 PB", $this->formatter->asSize(3 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000)); // this is 3 EB not 3 PB
        // tests for base 1024
        $this->formatter->sizeFormatBase = 1024;
        $this->assertSame("1 kibibyte", $this->formatter->asSize(1024));
        $this->assertSame("1 mebibyte", $this->formatter->asSize(1024 * 1024));
        $this->assertSame("1023 bytes", $this->formatter->asSize(1023));
        $this->assertSame("5 gibibytes", $this->formatter->asSize(5 * 1024 * 1024 * 1024));
        $this->assertNotEquals("5 pibibytes", $this->formatter->asSize(5 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024)); // this is 5 EiB not 5 PiB
        $this->assertSame("2 gibibytes", $this->formatter->asSize(2147483647)); // round 1.999 up to 2
        $this->formatter->decimalSeparator = ',';
        $this->formatter->numberFormatterOptions = [];
        $this->assertSame("1,001 kibibytes", $this->formatter->asSize(1025, 3));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asSize(null));
    }

    public function testAsSize()
    {
        // tests for base 1000
        $this->formatter->sizeFormatBase = 1000;
        $this->assertSame("999 bytes", $this->formatter->asSize(999));
        $this->assertSame("999 bytes", $this->formatter->asSize('999'));
        $this->assertSame("1.05 megabytes", $this->formatter->asSize(1024 * 1024));
        $this->assertSame("1.0486 megabytes", $this->formatter->asSize(1024 * 1024, 4));
        $this->assertSame("1.00 kilobyte", $this->formatter->asSize(1000));
        $this->assertSame("1.02 kilobytes", $this->formatter->asSize(1023));
        $this->assertSame("3.00 gigabytes", $this->formatter->asSize(3 * 1000 * 1000 * 1000));
        $this->assertNotEquals("3 PB", $this->formatter->asSize(3 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000)); // this is 3 EB not 3 PB
        // tests for base 1024
        $this->formatter->sizeFormatBase = 1024;
        $this->assertSame("1.00 kibibyte", $this->formatter->asSize(1024));
        $this->assertSame("1.00 mebibyte", $this->formatter->asSize(1024 * 1024));
        $this->assertSame("1023 bytes", $this->formatter->asSize(1023));
        $this->assertSame("5.00 gibibytes", $this->formatter->asSize(5 * 1024 * 1024 * 1024));
        $this->assertNotEquals("5.00 pibibytes", $this->formatter->asSize(5 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024)); // this is 5 EiB not 5 PiB
        $this->assertSame("2.00 gibibytes", $this->formatter->asSize(2147483647)); // round 1.999 up to 2
        $this->formatter->decimalSeparator = ',';
        $this->formatter->numberFormatterOptions = [];
        $this->assertSame("1,001 kibibytes", $this->formatter->asSize(1025, 3));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asSize(null));
    }

    public function testIntlAsSizeConfiguration()
    {
        $this->assertSame("1023 bytes", $this->formatter->asSize(1023));
        $this->assertSame("1023 B", $this->formatter->asShortSize(1023));
        $this->formatter->thousandSeparator = '.';
        $this->assertSame("1023 bytes", $this->formatter->asSize(1023));
        $this->assertSame("1023 B", $this->formatter->asShortSize(1023));
    }

    /**
     * https://github.com/yiisoft/yii2/issues/4960
     */
    public function testAsSizeConfiguration()
    {
        $this->assertSame("1023 bytes", $this->formatter->asSize(1023));
        $this->assertSame("1023 B", $this->formatter->asShortSize(1023));
        $this->formatter->thousandSeparator = '.';
        $this->assertSame("1023 bytes", $this->formatter->asSize(1023));
        $this->assertSame("1023 B", $this->formatter->asShortSize(1023));
    }
}
