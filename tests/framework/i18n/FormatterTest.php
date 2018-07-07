<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\i18n;

use Yii;
use yii\i18n\Formatter;
use yiiunit\TestCase;

/**
 * Test for basic formatter functions.
 *
 * See FormatterDateTest and FormatterNumberTest for date/number formatting.
 *
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
        $this->expectException('\yii\base\InvalidArgumentException');
        $this->assertSame(date('Y-m-d', $value), $this->formatter->format($value, 'data'));
        $this->assertSame(date('Y-m-d', $value), $this->formatter->format($value, function ($value) {
            return date('Y-m-d', $value);
        }));
        $this->assertSame('from: ' . date('Y-m-d', $value),
            $this->formatter->format($value, function ($value, $formatter) {
                /** @var $formatter Formatter */
                return 'from: ' . $formatter->asDate($value, 'php:Y-m-d');
            }));
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

    /*public function testAsHtml()
    {
        // todo: dependency on HtmlPurifier
    }*/

    public function testAsEmail()
    {
        $value = 'test@sample.com';
        $this->assertSame("<a href=\"mailto:$value\">$value</a>", $this->formatter->asEmail($value));
        $value = 'test@sample.com';
        $this->assertSame("<a href=\"mailto:$value\" target=\"_blank\">$value</a>",
            $this->formatter->asEmail($value, ['target' => '_blank']));

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
        $this->assertSame('<a href="https://www.yiiframework.com/?name=test&amp;value=5&quot;">https://www.yiiframework.com/?name=test&amp;value=5&quot;</a>',
            $this->formatter->asUrl($value));
        $value = 'http://www.yiiframework.com/';
        $this->assertSame("<a href=\"$value\" target=\"_blank\">$value</a>",
            $this->formatter->asUrl($value, ['target' => '_blank']));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asUrl(null));
    }

    public function testAsImage()
    {
        $value = 'http://sample.com/img.jpg';
        $this->assertSame("<img src=\"$value\" alt=\"\">", $this->formatter->asImage($value));
        $value = 'http://sample.com/img.jpg';
        $alt = 'Hello!';
        $this->assertSame("<img src=\"$value\" alt=\"$alt\">", $this->formatter->asImage($value, ['alt' => $alt]));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asImage(null));
    }

    public function testAsBoolean()
    {
        $this->assertSame('Yes', $this->formatter->asBoolean(true));
        $this->assertSame('No', $this->formatter->asBoolean(false));
        $this->assertSame('Yes', $this->formatter->asBoolean('111'));
        $this->assertSame('No', $this->formatter->asBoolean(''));
        $this->assertSame('No', $this->formatter->asBoolean(0));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asBoolean(null));
    }

    public function testAsTimestamp()
    {
        $this->assertSame('1451606400', $this->formatter->asTimestamp(1451606400));
        $this->assertSame('1451606400', $this->formatter->asTimestamp(1451606400.1234));
        $this->assertSame('1451606400', $this->formatter->asTimestamp(1451606400.0000));

        $this->assertSame('1451606400', $this->formatter->asTimestamp('1451606400'));
        $this->assertSame('1451606400', $this->formatter->asTimestamp('1451606400.1234'));
        $this->assertSame('1451606400', $this->formatter->asTimestamp('1451606400.0000'));

        $this->assertSame('1451606400', $this->formatter->asTimestamp('2016-01-01 00:00:00'));

        $dateTime = new \DateTime('2016-01-01 00:00:00.000');
        $this->assertSame('1451606400', $this->formatter->asTimestamp($dateTime));

        $dateTime = new \DateTime('2016-01-01 00:00:00.000', new \DateTimeZone('Europe/Berlin'));
        $this->assertSame('1451602800', $this->formatter->asTimestamp($dateTime));
    }

    public function lengthDataProvider()
    {
        return [
            [
                'Empty value gets proper output',
                [null], '<span class="not-set">(not set)</span>', '<span class="not-set">(not set)</span>',
            ],
            [
                'Wrong value is casted properly',
                ['NaN'], '0 millimeters', '0 mm',
                ['yii\base\InvalidArgumentException', "'NaN' is not a numeric value"],
            ],
            [
                'Negative value works',
                [-3], '-3 meters', '-3 m',
            ],
            [
                'Zero value works',
                [0], '0 millimeters', '0 mm',
            ],
            [
                'Decimal value is resolved in base units',
                [0.001], '1 millimeter', '1 mm',
            ],
            [
                'Decimal value smaller than minimum base unit gets rounded (#1)',
                [0.0004], '0 millimeters', '0 mm',
            ],
            [
                'Decimal value smaller than minimum base unit gets rounded (#2)',
                [0.00169], '2 millimeters', '2 mm',
            ],
            [
                'Integer value #1 works',
                [1], '1 meter', '1 m',
            ],
            [
                'Integer value #2 works',
                [453], '453 meters', '453 m',
            ],
            [
                'Double value works',
                [19913.13], '19.913 kilometers', '19.913 km',
            ],
            [
                'It is possible to change number of decimals',
                [19913.13, 1], '19.9 kilometers', '19.9 km',
            ],
            [
                'It is possible to change number formatting options',
                [100, null, [
                    \NumberFormatter::MIN_FRACTION_DIGITS => 4,
                ]], '100.0000 meters', '100.0000 m',
            ],
            [
                'It is possible to change text options',
                [-19913.13, null, null, [
                    \NumberFormatter::NEGATIVE_PREFIX => 'MINUS',
                ]], 'MINUS19.913 kilometers', 'MINUS19.913 km',
            ],
        ];
    }

    /**
     * @dataProvider lengthDataProvider
     * @param mixed $message
     * @param mixed $arguments
     * @param mixed $expected
     * @param mixed $_shortLength
     * @param mixed $expectedException
     */
    public function testIntlAsLength($message, $arguments, $expected, $_shortLength, $expectedException = [])
    {
        $this->ensureIntlUnitDataIsAvailable();
        if ($expectedException !== []) {
            $this->expectException($expectedException[0]);
            $this->expectExceptionMessage($expectedException[1]);
        }
        $this->assertSame($expected, call_user_func_array([$this->formatter, 'asLength'], $arguments), 'Failed asserting that ' . $message);
    }

    /**
     * @dataProvider lengthDataProvider
     * @param mixed $message
     * @param mixed $arguments
     * @param mixed $_length
     * @param mixed $expected
     * @param mixed $expectedException
     */
    public function testIntlAsShortLength($message, $arguments, $_length, $expected, $expectedException = [])
    {
        $this->ensureIntlUnitDataIsAvailable();
        if ($expectedException !== []) {
            $this->expectException($expectedException[0]);
            $this->expectExceptionMessage($expectedException[1]);
        }
        $this->assertSame($expected, call_user_func_array([$this->formatter, 'asShortLength'], $arguments), 'Failed asserting that ' . $message);
    }

    public function weightDataProvider()
    {
        return [
            [
                'Empty value gets proper output',
                [null], '<span class="not-set">(not set)</span>', '<span class="not-set">(not set)</span>',
            ],
            [
                'Wrong value is casted properly',
                ['NaN'], '0 grams', '0 g',
                ['yii\base\InvalidArgumentException', "'NaN' is not a numeric value"],
            ],
            [
                'Negative value works',
                [-3], '-3 kilograms', '-3 kg',
            ],
            [
                'Zero value works',
                [0], '0 grams', '0 g',
            ],
            [
                'Decimal value is resolved in base units',
                [0.001], '1 gram', '1 g',
            ],
            [
                'Decimal value smaller than minimum base unit gets rounded (#1)',
                [0.0004], '0 grams', '0 g',
            ],
            [
                'Decimal value smaller than minimum base unit gets rounded (#2)',
                [0.00169], '2 grams', '2 g',
            ],
            [
                'Integer value #1 works',
                [1], '1 kilogram', '1 kg',
            ],
            [
                'Integer value #2 works',
                [453], '453 kilograms', '453 kg',
            ],
            [
                'Double value works',
                [19913.13], '19.913 tons', '19.913 tn',
            ],
            [
                'It is possible to change number of decimals',
                [19913.13, 1], '19.9 tons', '19.9 tn',
            ],
            [
                'It is possible to change number formatting options',
                [100, null, [
                    \NumberFormatter::MIN_FRACTION_DIGITS => 4,
                ]], '100.0000 kilograms', '100.0000 kg',
            ],
            [
                'It is possible to change text options',
                [-19913.13, null, null, [
                    \NumberFormatter::NEGATIVE_PREFIX => 'MINUS',
                ]], 'MINUS19.913 tons', 'MINUS19.913 tn',
            ],
        ];
    }

    /**
     * @dataProvider weightDataProvider
     * @param mixed $message
     * @param mixed $arguments
     * @param mixed $expected
     * @param mixed $_shortWeight
     * @param mixed $expectedException
     */
    public function testIntlAsWeight($message, $arguments, $expected, $_shortWeight, $expectedException = [])
    {
        $this->ensureIntlUnitDataIsAvailable();
        if ($expectedException !== []) {
            $this->expectException($expectedException[0]);
            $this->expectExceptionMessage($expectedException[1]);
        }
        $this->assertSame($expected, call_user_func_array([$this->formatter, 'asWeight'], $arguments), 'Failed asserting that ' . $message);
    }

    /**
     * @dataProvider weightDataProvider
     * @param mixed $message
     * @param mixed $arguments
     * @param mixed $_weight
     * @param mixed $expected
     * @param mixed $expectedException
     */
    public function testIntlAsShortWeight($message, $arguments, $_weight, $expected, $expectedException = [])
    {
        $this->ensureIntlUnitDataIsAvailable();
        if ($expectedException !== []) {
            $this->expectException($expectedException[0]);
            $this->expectExceptionMessage($expectedException[1]);
        }
        $this->assertSame($expected, call_user_func_array([$this->formatter, 'asShortWeight'], $arguments), 'Failed asserting that ' . $message);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage Format of mass is only supported when PHP intl extension is installed.
     */
    public function testAsWeight()
    {
        $this->formatter->asWeight(10);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage Format of length is only supported when PHP intl extension is installed.
     */
    public function testAsLength()
    {
        $this->formatter->asShortLength(10);
    }

    protected function ensureIntlUnitDataIsAvailable()
    {
        $skip = function () {
            $this->markTestSkipped('ICU data does not contain measure units information.');
        };

        try {
            $bundle = new \ResourceBundle($this->formatter->locale, 'ICUDATA-unit');
            $massUnits = $bundle['units']['mass'];
            $lengthUnits = $bundle['units']['length'];

            if ($massUnits === null || $lengthUnits === null) {
                $skip();
            }
        } catch (\IntlException $e) {
            $skip();
        }
    }
}
