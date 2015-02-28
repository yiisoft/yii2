<?php

namespace yiiunit\framework\helpers;

use Yii;
use yii\helpers\FormatConverter;
use yii\i18n\Formatter;
use yiiunit\framework\i18n\IntlTestHelper;
use yiiunit\TestCase;

/**
 * @group helpers
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
        $this->assertEquals('n/j/y', FormatConverter::convertDateIcuToPhp('short', 'date', 'en-US'));
        $this->assertEquals('d.m.y', FormatConverter::convertDateIcuToPhp('short', 'date', 'de-DE'));
    }

    public function testEscapedIcuToPhp()
    {
        $this->assertEquals('\\o\\\'\\c\\l\\o\\c\\k', FormatConverter::convertDateIcuToPhp('\'o\'\'clock\''));
    }

    public function testEscapedIcuToJui()
    {
        $this->assertEquals('\'o\'\'clock\'', FormatConverter::convertDateIcuToJui('\'o\'\'clock\''));
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
