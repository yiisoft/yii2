<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\base\Object;
use yii\web\Response;
use yii\web\XmlResponseFormatter;

class Post extends Object
{
	public $id;
	public $title;

	public function __construct($id, $title)
	{
		$this->id = $id;
		$this->title = $title;
	}
}

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class XmlResponseFormatterTest extends \yiiunit\TestCase
{
	/**
	 * @var Response
	 */
	public $response;
	/**
	 * @var XmlResponseFormatter
	 */
	public $formatter;

	protected function setUp()
	{
		$this->mockApplication();
		$this->response = new Response;
		$this->formatter = new XmlResponseFormatter;
	}

	public function testFormatScalars()
	{
		$head = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

		$xml = $head . "<response></response>\n";
		$this->assertEquals($xml, $this->formatter->format($this->response, null));

		$xml = $head . "<response>1</response>\n";
		$this->assertEquals($xml, $this->formatter->format($this->response, 1));

		$xml = $head . "<response>abc</response>\n";
		$this->assertEquals($xml, $this->formatter->format($this->response, 'abc'));

		$xml = $head . "<response>1</response>\n";
		$this->assertEquals($xml, $this->formatter->format($this->response, true));
	}

	public function testFormatArrays()
	{
		$head = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

		$xml = $head . "<response/>\n";
		$this->assertEquals($xml, $this->formatter->format($this->response, array()));

		$xml = $head . "<response><item>1</item><item>abc</item></response>\n";
		$this->assertEquals($xml, $this->formatter->format($this->response, array(1, 'abc')));

		$xml = $head . "<response><a>1</a><b>abc</b></response>\n";
		$this->assertEquals($xml, $this->formatter->format($this->response, array(
			'a' => 1,
			'b' => 'abc',
		)));

		$xml = $head . "<response><item>1</item><item>abc</item><item><item>2</item><item>def</item></item><item>1</item></response>\n";
		$this->assertEquals($xml, $this->formatter->format($this->response, array(
			1,
			'abc',
			array(2, 'def'),
			true,
		)));

		$xml = $head . "<response><a>1</a><b>abc</b><c><item>2</item><item>def</item></c><item>1</item></response>\n";
		$this->assertEquals($xml, $this->formatter->format($this->response, array(
			'a' => 1,
			'b' => 'abc',
			'c' => array(2, 'def'),
			true,
		)));
	}

	public function testFormatObjects()
	{
		$head = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

		$xml = $head . "<response><Post><id>123</id><title>abc</title></Post></response>\n";
		$this->assertEquals($xml, $this->formatter->format($this->response, new Post(123, 'abc')));

		$xml = $head . "<response><Post><id>123</id><title>abc</title></Post><Post><id>456</id><title>def</title></Post></response>\n";
		$this->assertEquals($xml, $this->formatter->format($this->response, array(
			new Post(123, 'abc'),
			new Post(456, 'def'),
		)));

		$xml = $head . "<response><Post><id>123</id><title>abc</title></Post><a><Post><id>456</id><title>def</title></Post></a></response>\n";
		$this->assertEquals($xml, $this->formatter->format($this->response, array(
			new Post(123, 'abc'),
			'a' => new Post(456, 'def'),
		)));
	}
}
