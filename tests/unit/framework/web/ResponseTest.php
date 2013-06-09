<?php

namespace yiiunit\framework\web;

use Yii;
use yii\helpers\StringHelper;
use yii\web\Response;

class ResponseTest extends \yiiunit\TestCase
{
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
	public function testSendFileRanges($rangeHeader, $expectedHeader, $length, $expectedFile)
	{
		$content = $this->generateTestFileContent();
		$_SERVER['HTTP_RANGE'] = 'bytes=' . $rangeHeader;
		$this->response->sendFile('testFile.txt', $content, null, false);

		$this->assertEquals($expectedFile, $this->response->content);
		$this->assertEquals(206, $this->response->statusCode);
		$headers = $this->response->headers;
		$this->assertEquals("bytes", $headers->get('Accept-Ranges'));
		$this->assertEquals("bytes " . $expectedHeader . '/' . StringHelper::strlen($content), $headers->get('Content-Range'));
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
		$this->setExpectedException('yii\base\HttpException', 'Requested Range Not Satisfiable');

		$content = $this->generateTestFileContent();
		$_SERVER['HTTP_RANGE'] = 'bytes=' . $rangeHeader;
		$this->response->sendFile('testFile.txt', $content, null, false);
	}

	protected function generateTestFileContent()
	{
		return '12ёжик3456798áèabcdefghijklmnopqrstuvwxyz!"§$%&/(ёжик)=?';
	}
}
