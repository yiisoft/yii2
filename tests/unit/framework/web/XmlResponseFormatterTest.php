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
 *
 * @group web
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

	/**
	 * @param mixed $data the data to be formatted 
	 * @param string $xml the expected XML body
	 * @dataProvider formatScalarDataProvider
	 */
	public function testFormatScalar($data, $xml)
	{
		$head = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$this->response->data = $data;
		$this->formatter->format($this->response);
		$this->assertEquals($head . $xml, $this->response->content);
	}
	
	public function formatScalarDataProvider()
	{
		return array(
			array(null, "<response></response>\n"),
			array(1, "<response>1</response>\n"),
			array('abc', "<response>abc</response>\n"),
			array(true, "<response>1</response>\n"),
			array("<>", "<response>&lt;&gt;</response>\n"),
		);
	}

	/**
	 * @param mixed $data the data to be formatted
	 * @param string $xml the expected XML body
	 * @dataProvider formatArrayDataProvider
	 */
	public function testFormatArrays($data, $xml)
	{
		$head = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$this->response->data = $data;
		$this->formatter->format($this->response);
		$this->assertEquals($head . $xml, $this->response->content);
	}

	public function formatArrayDataProvider()
	{
		return array(
			array(array(), "<response/>\n"),
			array(array(1, 'abc'), "<response><item>1</item><item>abc</item></response>\n"),
			array(array(
				'a' => 1,
				'b' => 'abc',
			), "<response><a>1</a><b>abc</b></response>\n"),
			array(array(
				1,
				'abc',
				array(2, 'def'),
				true,
			), "<response><item>1</item><item>abc</item><item><item>2</item><item>def</item></item><item>1</item></response>\n"),
			array(array(
				'a' => 1,
				'b' => 'abc',
				'c' => array(2, '<>'),
				true,
			), "<response><a>1</a><b>abc</b><c><item>2</item><item>&lt;&gt;</item></c><item>1</item></response>\n"),
		);
	}

	/**
	 * @param mixed $data the data to be formatted
	 * @param string $xml the expected XML body
	 * @dataProvider formatObjectDataProvider
	 */
	public function testFormatObjects($data, $xml)
	{
		$head = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$this->response->data = $data;
		$this->formatter->format($this->response);
		$this->assertEquals($head . $xml, $this->response->content);
	}

	public function formatObjectDataProvider()
	{
		return array(
			array(new Post(123, 'abc'), "<response><Post><id>123</id><title>abc</title></Post></response>\n"),
			array(array(
				new Post(123, 'abc'),
				new Post(456, 'def'),
			), "<response><Post><id>123</id><title>abc</title></Post><Post><id>456</id><title>def</title></Post></response>\n"),
			array(array(
				new Post(123, '<>'),
				'a' => new Post(456, 'def'),
			), "<response><Post><id>123</id><title>&lt;&gt;</title></Post><a><Post><id>456</id><title>def</title></Post></a></response>\n"),
		);
	}
}
