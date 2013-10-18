<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace yiiunit\framework\base;

use yii\base\Formatter;
use yiiunit\TestCase;

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
		$this->assertSame(date('Y/m/d', $value), $this->formatter->asDate($value));
		$this->assertSame(date('Y-m-d', $value), $this->formatter->asDate($value, 'Y-m-d'));
		$this->assertSame($this->formatter->nullDisplay, $this->formatter->asDate(null));
	}

	public function testAsTime()
	{
		$value = time();
		$this->assertSame(date('h:i:s A', $value), $this->formatter->asTime($value));
		$this->assertSame(date('h:i:s', $value), $this->formatter->asTime($value, 'h:i:s'));
		$this->assertSame($this->formatter->nullDisplay, $this->formatter->asTime(null));
	}

	public function testAsDatetime()
	{
		$value = time();
		$this->assertSame(date('Y/m/d h:i:s A', $value), $this->formatter->asDatetime($value));
		$this->assertSame(date('Y-m-d h:i:s', $value), $this->formatter->asDatetime($value, 'Y-m-d h:i:s'));
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
		$value = 123.12;
		$this->assertSame("123,12", $this->formatter->asDouble($value));
		$this->assertSame("123,1", $this->formatter->asDouble($value, 1));
		$this->assertSame("123", $this->formatter->asDouble($value, 0));
		$value = 123123.123;
		$this->assertSame("123123,12", $this->formatter->asDouble($value));
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

	public function testFormat()
	{
		$value = time();
		$this->assertSame(date('Y/m/d', $value), $this->formatter->format($value, 'date'));
		$this->assertSame(date('Y/m/d', $value), $this->formatter->format($value, 'DATE'));
		$this->assertSame(date('Y-m-d', $value), $this->formatter->format($value, array('date', 'Y-m-d')));
		$this->setExpectedException('\yii\base\InvalidParamException');
		$this->assertSame(date('Y-m-d', $value), $this->formatter->format($value, 'data'));
	}
}
