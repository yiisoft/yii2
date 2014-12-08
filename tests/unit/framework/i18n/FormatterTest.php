<?php

namespace yiiunit\framework\i18n;

use yii\i18n\Formatter;
use Yii;
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
        $value = 'test@sample.com';
        $this->assertSame("<a href=\"mailto:$value\" target=\"_blank\">$value</a>", $this->formatter->asEmail($value, ['target' => '_blank']));

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
        $value = 'http://www.yiiframework.com/';
        $this->assertSame("<a href=\"$value\" target=\"_blank\">$value</a>", $this->formatter->asUrl($value, ['target' => '_blank']));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asUrl(null));
    }

    public function testAsImage()
    {
        $value = 'http://sample.com/img.jpg';
        $this->assertSame("<img src=\"$value\" alt=\"\">", $this->formatter->asImage($value));
        $value = 'http://sample.com/img.jpg';
        $alt = "Hello!";
        $this->assertSame("<img src=\"$value\" alt=\"$alt\">", $this->formatter->asImage($value, ['alt' => $alt]));

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
}
