<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
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
