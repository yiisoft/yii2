<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace yiiunit\framework\base;

use yii\base\Formatter;
use yiiunit\TestCase;
use DateTime;
use DateInterval;

/**
 * @group base
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
        $this->mockApplication();
        $this->formatter = new Formatter();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->formatter = null;
    }

    public function testAsRaw()
    {
        $value = '123';
        $this->assertSame($value, $this->formatter->asRaw($value));
        $value = 123;
        $this->assertSame($value, $this->formatter->asRaw($value));
        $value = '<>';
        $this->assertSame($value, $this->formatter->asRaw($value));
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
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asEmail(null));
    }

    public function testAsImage()
    {
        $value = 'http://sample.com/img.jpg';
        $this->assertSame("<img src=\"$value\" alt=\"\">", $this->formatter->asImage($value));
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asImage(null));
    }

    public function testAsBoolean()
    {
        $value = true;
        $this->assertSame('Yes', $this->formatter->asBoolean($value));
        $value = false;
        $this->assertSame('No', $this->formatter->asBoolean($value));
        $value = "111";
        $this->assertSame('Yes', $this->formatter->asBoolean($value));
        $value = "";
        $this->assertSame('No', $this->formatter->asBoolean($value));
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asBoolean(null));
    }

    public function testAsDate()
    {
        $value = time();
        $this->assertSame(date('M j, Y', $value), $this->formatter->asDate($value));
        $this->assertSame(date('Y/m/d', $value), $this->formatter->asDate($value, 'Y/m/d'));
        $this->assertSame(date('n/j/y', $value), $this->formatter->asDate($value, 'short'));
        $this->assertSame(date('F j, Y', $value), $this->formatter->asDate($value, 'long'));
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asDate(null));
    }

    public function testAsTime()
    {
        $value = time();
        $this->assertSame(date('g:i:s A', $value), $this->formatter->asTime($value));
        $this->assertSame(date('n:i:s A', $value), $this->formatter->asTime($value, 'n:i:s A'));
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asTime(null));
    }

    public function testAsDatetime()
    {
        $value = time();
        $this->assertSame(date('M j, Y, g:i:s A', $value), $this->formatter->asDatetime($value));
        $this->assertSame(date('Y/m/d h:i:s A', $value), $this->formatter->asDatetime($value, 'Y/m/d h:i:s A'));
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asDatetime(null));
    }

    public function testAsInteger()
    {
        $value = 123;
        $this->assertSame("$value", $this->formatter->asInteger($value));
        $value = 123.23;
        $this->assertSame("123", $this->formatter->asInteger($value));
        $value = 'a';
        $this->assertSame("0", $this->formatter->asInteger($value));
        $value = -123.23;
        $this->assertSame("-123", $this->formatter->asInteger($value));
        $value = "-123abc";
        $this->assertSame("-123", $this->formatter->asInteger($value));
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asInteger(null));
    }

    public function testAsDouble()
    {
        $value = 123.12;
        $this->assertSame("123.12", $this->formatter->asDouble($value));
        $this->assertSame("123.1", $this->formatter->asDouble($value, 1));
        $this->assertSame("123", $this->formatter->asDouble($value, 0));
        $value = 123;
        $this->assertSame("123.00", $this->formatter->asDouble($value));
        $this->formatter->decimalSeparator = ',';
        $this->formatter->thousandSeparator = '.';
        $value = 123.12;
        $this->assertSame("123,12", $this->formatter->asDouble($value));
        $this->assertSame("123,1", $this->formatter->asDouble($value, 1));
        $this->assertSame("123", $this->formatter->asDouble($value, 0));
        $value = 123123.123;
        $this->assertSame("123.123,12", $this->formatter->asDouble($value));
        $this->assertSame("123123,12", $this->formatter->asDouble($value, 2, null, false));
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asDouble(null));
    }

    public function testAsNumber()
    {
        $value = 123123.123;
        $this->assertSame("123,123", $this->formatter->asNumber($value));
        $this->assertSame("123,123.12", $this->formatter->asNumber($value, 2));
        $this->formatter->decimalSeparator = ',';
        $this->formatter->thousandSeparator = ' ';
        $this->assertSame("123 123", $this->formatter->asNumber($value));
        $this->assertSame("123 123,12", $this->formatter->asNumber($value, 2));
        $this->formatter->thousandSeparator = '';
        $this->assertSame("123123", $this->formatter->asNumber($value));
        $this->assertSame("123123,12", $this->formatter->asNumber($value, 2));
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asNumber(null));
    }

    public function testAsDecimal()
    {
        $value = '123';
        $this->assertSame($value, $this->formatter->asDecimal($value));
        $value = '123456';
        $this->assertSame("123,456", $this->formatter->asDecimal($value));
        $value = '-123456.123';
        $this->assertSame("-123,456.123", $this->formatter->asDecimal($value));
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asDecimal(null));
    }

    public function testAsCurrency()
    {
        $value = '123';
        $this->assertSame('$123.00', $this->formatter->asCurrency($value));
        $value = '123.456';
        $this->assertSame("$123.46", $this->formatter->asCurrency($value));
        // Starting from ICU 52.1, negative currency value will be formatted as -$123,456.12
        // see: http://source.icu-project.org/repos/icu/icu/tags/release-52-1/source/data/locales/en.txt
//		$value = '-123456.123';
//		$this->assertSame("($123,456.12)", $this->formatter->asCurrency($value));
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asCurrency(null));
    }

    public function testAsScientific()
    {
        $value = '123';
        $this->assertSame('1.23E2', $this->formatter->asScientific($value));
        $value = '123456';
        $this->assertSame("1.23456E5", $this->formatter->asScientific($value));
        $value = '-123456.123';
        $this->assertSame("-1.23456123E5", $this->formatter->asScientific($value));
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asScientific(null));
    }

    public function testAsPercent()
    {
        $value = '123';
        $this->assertSame('12,300%', $this->formatter->asPercent($value));
        $value = '0.1234';
        $this->assertSame("12%", $this->formatter->asPercent($value));
        $value = '-0.009343';
        $this->assertSame("-1%", $this->formatter->asPercent($value));
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asPercent(null));
    }

    public function testFormat()
    {
        $value = time();
        $this->assertSame(date('M j, Y', $value), $this->formatter->format($value, 'date'));
        $this->assertSame(date('M j, Y', $value), $this->formatter->format($value, 'DATE'));
        $this->assertSame(date('Y/m/d', $value), $this->formatter->format($value, ['date', 'Y/m/d']));
        $this->setExpectedException('\yii\base\InvalidParamException');
        $this->assertSame(date('Y-m-d', $value), $this->formatter->format($value, 'data'));
    }

    private function buildDateSubIntervals($referenceDate, $intervals)
    {
        $date = new DateTime($referenceDate);
        foreach ($intervals as $interval) {
            $date->sub($interval);
        }
        return $date;
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
    }
    
    public function testSetLocale(){
        $value = '12300';
        $this->formatter->setLocale('de-DE');
        $this->assertSame('12.300,00', $this->formatter->asDouble($value, 2));
        $value = time();
        $this->assertSame(date('d.m.Y', $value), $this->formatter->asDate($value));
        $this->formatter->setLocale('en-US');
        
    }
}
