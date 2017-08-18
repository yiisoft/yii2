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
 * Test for basic formatter functions
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
        $this->expectException('\yii\base\InvalidParamException');
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
            [-3, '-3 meters', '-3 m'],
            ['NaN', '0 millimeters', '0 mm'],
            [0, '0 millimeters', '0 mm'],
            [0.005, '5 millimeters', '5 mm'],
            [0.053, '5.3 centimeters', '5.3 cm'],
            [0.1, '10 centimeters', '10 cm'],
            [1.123, '1.123 meters', '1.123 m'],
            [1893.12, '1.893 kilometers', '1.893 km'],
            [4561549, '4561.549 kilometers', '4561.549 km'],
        ];
    }

    /**
     * @param $value
     * @param $expected
     *
     * @dataProvider lengthDataProvider
     */
    public function testIntlAsLength($value, $expected)
    {
        $this->ensureIntlUnitDataAvailable();
        $this->assertSame($expected, $this->formatter->asLength($value));
    }

    /**
     * @param $value
     * @param $expected
     *
     * @dataProvider lengthDataProvider
     */
    public function testIntlAsShortLength($value, $_, $expected)
    {
        $this->ensureIntlUnitDataAvailable();
        $this->assertSame($expected, $this->formatter->asShortLength($value));
    }

    public function weightDataProvider()
    {
        return [
            [null, '<span class="not-set">(not set)</span>', '<span class="not-set">(not set)</span>'],
            ['NaN', '0 grams', '0 g'],
            [-3, '-3 kilograms', '-3 kg'],
            [0, '0 grams', '0 g'],
            [0.001, '1 gram', '1 g'],
            [0.091, '91 grams', '91 g'],
            [0.1, '100 grams', '100 g'],
            [1, '1 kilogram', '1 kg'],
            [453, '453 kilograms', '453 kg'],
            [19913.13, '19.913 tons', '19.913 tn'],
        ];
    }

    /**
     * @param $value
     * @param $expected
     *
     * @dataProvider weightDataProvider
     */
    public function testIntlAsWeight($value, $expected)
    {
        $this->ensureIntlUnitDataAvailable();
        $this->assertSame($expected, $this->formatter->asWeight($value));
    }

    /**
     * @param $value
     * @param $expected
     *
     * @dataProvider weightDataProvider
     */
    public function testIntlAsShortWeight($value, $_, $expected)
    {
        $this->ensureIntlUnitDataAvailable();
        $this->assertSame($expected, $this->formatter->asShortWeight($value));
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

    protected function ensureIntlUnitDataAvailable()
    {
        $skip = function () {
            $this->markTestSkipped('ICU data does not contain measure units information.');
        };

        if (defined('HHVM_VERSION')) {
            return $skip();
        }

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
