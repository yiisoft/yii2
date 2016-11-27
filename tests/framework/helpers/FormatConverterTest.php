<?php

namespace yiiunit\framework\helpers;

use DateTime;
use IntlDateFormatter;
use Yii;
use yii\base\Exception;
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

    /**
     * ensure PHP fallback matches the ICU format for en_US
     */
    public function testPHPDefaultFormat()
    {

        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Can not test on HHVM because HHVM returns inconsistend with PHP date format patterns.');
        }

        foreach (FormatConverter::$phpFallbackDatePatterns as $format => $formats) {
            foreach ($formats as $name => $expected) {
                $expected = FormatConverter::convertDatePhpToIcu($expected);
                $expected = str_replace('e', 'E', $expected); // seems to be equal
                $expected = str_replace('yyyy', 'y', $expected); // this is equal
                if ($format === 'full') {
                    $expected = str_replace('zzz', 'zzzz', $expected); // there is no php representation for zzzz so we use zzz instead
                } else {
                    $expected = str_replace('zzz', 'z', $expected); // this is equal
                }

                switch ($name) {
                    case 'date':
                        $fmt = new IntlDateFormatter(
                            'en_US',
                            $this->convertFormat($format),
                            IntlDateFormatter::NONE,
                            'UTC'
                        );
                        break;
                    case 'time':
                        $fmt = new IntlDateFormatter(
                            'en_US',
                            IntlDateFormatter::NONE,
                            $this->convertFormat($format),
                            'UTC'
                        );
                        break;
                    case 'datetime':
                        $fmt = new IntlDateFormatter(
                            'en_US',
                            $this->convertFormat($format),
                            $this->convertFormat($format),
                            'UTC'
                        );
                        break;
                    default:
                        throw new Exception("Format \"$name\" is not supported");
                        break;
                }

                $this->assertEquals($expected, $fmt->getPattern(), "Format for $format $name does not match.");
            }
        }
    }

    private function convertFormat($format)
    {
        switch ($format) {
            case 'short':
                return IntlDateFormatter::SHORT;
            case 'medium':
                return IntlDateFormatter::MEDIUM;
            case 'long':
                return IntlDateFormatter::LONG;
            case 'full':
                return IntlDateFormatter::FULL;
        }

        return null;
    }

    public function testIntlIcuToPhpShortForm()
    {
        $this->assertEquals('n/j/y', FormatConverter::convertDateIcuToPhp('short', 'date', 'en-US'));
        $this->assertEquals('d.m.y', FormatConverter::convertDateIcuToPhp('short', 'date', 'de-DE'));
    }

    public function testEscapedIcuToPhp()
    {
        $this->assertEquals('l, F j, Y \\a\\t g:i:s a T', FormatConverter::convertDateIcuToPhp('EEEE, MMMM d, y \'at\' h:mm:ss a zzzz'));
        $this->assertEquals('\\o\\\'\\c\\l\\o\\c\\k', FormatConverter::convertDateIcuToPhp('\'o\'\'clock\''));
    }

    public function testEscapedIcuToJui()
    {
        $this->assertEquals('l, F j, Y \\a\\t g:i:s a T', FormatConverter::convertDateIcuToPhp('EEEE, MMMM d, y \'at\' h:mm:ss a zzzz'));
        $this->assertEquals('\'o\'\'clock\'', FormatConverter::convertDateIcuToJui('\'o\'\'clock\''));
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
        $this->assertEquals('d M Y \г.', FormatConverter::convertDateIcuToPhp('dd MMM y \'г\'.', 'date', 'ru-RU'));
        $this->assertEquals('dd M yy \'г\'.', FormatConverter::convertDateIcuToJui('dd MMM y \'г\'.', 'date', 'ru-RU'));

        $formatter = new Formatter(['locale' => 'ru-RU']);
        // There is a dot after month name in updated ICU data and no dot in old data. Both are acceptable.
        // See https://github.com/yiisoft/yii2/issues/9906
        $this->assertRegExp('/24 авг\.? 2014 г\./', $formatter->asDate('2014-8-24', 'dd MMM y \'г\'.'));
    }
}
