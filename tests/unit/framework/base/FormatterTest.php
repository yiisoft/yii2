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
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
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
	}

	public function testAsText()
	{
		$value = '123';
		$this->assertSame($value, $this->formatter->asText($value));
		$value = 123;
		$this->assertSame("$value", $this->formatter->asText($value));
		$value = '<>';
		$this->assertSame('&lt;&gt;', $this->formatter->asText($value));
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
	}

	public function testAsHtml()
	{
		// todo
	}

	public function testAsEmail()
	{
		$value = 'test@sample.com';
		$this->assertSame("<a href=\"mailto:$value\">$value</a>", $this->formatter->asEmail($value));
	}

	public function testAsImage()
	{
		$value = 'http://sample.com/img.jpg';
		$this->assertSame("<img src=\"$value\" alt=\"\" />", $this->formatter->asImage($value));
	}
}
