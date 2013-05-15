<?php

namespace yii\web;

use yiiunit\framework\web\ResponseTest;

/**
 * Mock PHP header function to check for sent headers
 * @param string $string
 * @param bool $replace
 * @param int $httpResponseCode
 */
function header($string, $replace = true, $httpResponseCode = null) {
	ResponseTest::$headers[] = $string;
	// TODO implement replace

	if ($httpResponseCode !== null) {
		ResponseTest::$httpResponseCode = $httpResponseCode;
	}
}

namespace yiiunit\framework\web;

use yii\helpers\StringHelper;
use yii\web\Response;

class ResponseTest extends \yiiunit\TestCase
{
	public static $headers = array();
	public static $httpResponseCode = 200;

	protected function setUp()
	{
		parent::setUp();
		$this->reset();
	}

	protected function reset()
	{
		static::$headers = array();
		static::$httpResponseCode = 200;
	}

	public function ranges()
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
	 * @dataProvider ranges
	 */
	public function testSendFileRanges($rangeHeader, $expectedHeader, $length, $expectedFile)
	{
		$content = $this->generateTestFileContent();

		$_SERVER['HTTP_RANGE'] = 'bytes=' . $rangeHeader;
		$sent = $this->runSendFile('testFile.txt', $content, null);
		$this->assertEquals($expectedFile, $sent);
		$this->assertTrue(in_array('HTTP/1.1 206 Partial Content', static::$headers));
		$this->assertTrue(in_array('Accept-Ranges: bytes', static::$headers));
		$this->assertArrayHasKey('Content-Range: bytes ' . $expectedHeader . '/' . StringHelper::strlen($content), array_flip(static::$headers));
		$this->assertTrue(in_array('Content-Type: text/plain', static::$headers));
		$this->assertTrue(in_array('Content-Length: ' . $length, static::$headers));
	}

	protected function generateTestFileContent()
	{
		return '12ёжик3456798áèabcdefghijklmnopqrstuvwxyz!"§$%&/(ёжик)=?';
	}

	protected function runSendFile($fileName, $content, $mimeType)
	{
		ob_start();
		ob_implicit_flush(false);
		$response = new Response();
		$response->sendFile($fileName, $content, $mimeType, false);
		$file = ob_get_clean();
		return $file;
	}
}