<?php

// override information about intl
namespace yii\helpers {
    use yiiunit\framework\i18n\FormatterTest;
    if (!function_exists('yii\helpers\extension_loaded')) {
        function extension_loaded($name)
        {
            if ($name === 'intl' && FormatterTest::$enableIntl !== null) {
                return FormatterTest::$enableIntl;
            }
            return \extension_loaded($name);
        }
    }
}

namespace yiiunit\framework\helpers {

use Yii;
use yii\helpers\FormatConverter;
    use yii\i18n\Formatter;
    use yiiunit\framework\i18n\FormatterTest;
use yiiunit\TestCase;

/**
 * @group helpers
 */
class FormatConverterTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        // emulate disabled intl extension
        // enable it only for tests prefixed with testIntl
        FormatterTest::$enableIntl = null;
        if (strncmp($this->getName(false), 'testIntl', 8) === 0) {
            if (!extension_loaded('intl')) {
                $this->markTestSkipped('intl extension is not installed.');
            }
            FormatterTest::$enableIntl = true;
        } else {
            FormatterTest::$enableIntl = false;
        }

        $this->mockApplication([
            'timeZone' => 'UTC',
            'language' => 'ru-RU',
        ]);
    }

    protected function tearDown()
    {
        parent::tearDown();
        FormatterTest::$enableIntl = null;
    }

    public function testIntlIcuToPhpShortForm()
    {
        $this->assertEquals('m/j/y', FormatConverter::convertDateIcuToPhp('short', 'date', 'en-US'));
        $this->assertEquals('d.m.y', FormatConverter::convertDateIcuToPhp('short', 'date', 'de-DE'));
    }

    public function testIntlOneDigitIcu()
    {
        $formatter = new Formatter(['locale' => 'en-US']);
        $this->assertEquals('24.8.2014', $formatter->asDate('2014-8-24', 'php:d.n.Y'));
        $this->assertEquals('24.8.2014', $formatter->asDate('2014-8-24', 'd.M.yyyy'));
    }

    public function testOneDigitIcu()
    {
        $formatter = new Formatter(['locale' => 'en-US']);
        $this->assertEquals('24.8.2014', $formatter->asDate('2014-8-24', 'php:d.n.Y'));
        $this->assertEquals('24.8.2014', $formatter->asDate('2014-8-24', 'd.M.yyyy'));
    }
}
}