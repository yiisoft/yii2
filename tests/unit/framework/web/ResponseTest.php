<?php

namespace yiiunit\framework\web;

use Yii;
use yii\helpers\StringHelper;

class Response extends \yii\web\Response
{
	public function send()
	{
		// does nothing to allow testing
	}
}

class ResponseTest extends \yiiunit\TestCase
{
	/**
	 * @var Response
	 */
	public $response;

	protected function setUp()
	{
		parent::setUp();
		$this->mockApplication();
		$this->response = new Response;
	}

	public function rightRanges()
	{
		// TODO test more cases for range requests and check for rfc compatibility
		// http://www.w3.org/Protocols/rfc2616/rfc2616.txt
		return array(
			array('0-5', '0-5', 6, '12ёж'),
			array('2-', '2-66', 65, 'ёжик3456798áèabcdefghijklmnopqrstuvwxyz!"§$%&/(ёжик)=?'),
			array('-12', '55-66', 12, '(ёжик)=?'),
		);
	}

	/**
	 * @dataProvider rightRanges
	 */
	public function testSendFileRanges($rangeHeader, $expectedHeader, $length, $expectedContent)
	{
		$dataFile = \Yii::getAlias('@yiiunit/data/web/data.txt');
		$fullContent = file_get_contents($dataFile);
		$_SERVER['HTTP_RANGE'] = 'bytes=' . $rangeHeader;
		ob_start();
		$this->response->sendFile($dataFile);
		$content = ob_get_clean();

		$this->assertEquals($expectedContent, $content);
		$this->assertEquals(206, $this->response->statusCode);
		$headers = $this->response->headers;
		$this->assertEquals("bytes", $headers->get('Accept-Ranges'));
		$this->assertEquals("bytes " . $expectedHeader . '/' . StringHelper::strlen($fullContent), $headers->get('Content-Range'));
		$this->assertEquals('text/plain', $headers->get('Content-Type'));
		$this->assertEquals("$length", $headers->get('Content-Length'));
	}

	public function wrongRanges()
	{
		// TODO test more cases for range requests and check for rfc compatibility
		// http://www.w3.org/Protocols/rfc2616/rfc2616.txt
		return array(
			array('1-2,3-5,6-10'),	// multiple range request not supported
			array('5-1'),			// last-byte-pos value is less than its first-byte-pos value
			array('-100000'),		// last-byte-pos bigger then content length
			array('10000-'),			// first-byte-pos bigger then content length
		);
	}

	/**
	 * @dataProvider wrongRanges
	 */
	public function testSendFileWrongRanges($rangeHeader)
	{
		$this->setExpectedException('yii\web\HttpException');

		$dataFile = \Yii::getAlias('@yiiunit/data/web/data.txt');
		$_SERVER['HTTP_RANGE'] = 'bytes=' . $rangeHeader;
		$this->response->sendFile($dataFile);
	}

	protected function generateTestFileContent()
	{
		return '12ёжик3456798áèabcdefghijklmnopqrstuvwxyz!"§$%&/(ёжик)=?';
	}
}
