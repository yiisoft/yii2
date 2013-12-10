<?php

namespace yiiunit\extensions\authclient\oauth\signature;

use yiiunit\extensions\authclient\TestCase;

class BaseMethodTest extends TestCase
{
	/**
	 * Creates test signature method instance.
	 * @return \yii\authclient\oauth\signature\BaseMethod
	 */
	protected function createTestSignatureMethod()
	{
		$signatureMethod = $this->getMock('\yii\authclient\oauth\signature\BaseMethod', ['getName', 'generateSignature']);
		$signatureMethod->expects($this->any())->method('getName')->will($this->returnValue('testMethodName'));
		$signatureMethod->expects($this->any())->method('generateSignature')->will($this->returnValue('testSignature'));
		return $signatureMethod;
	}

	// Tests :

	public function testGenerateSignature()
	{
		$signatureMethod = $this->createTestSignatureMethod();

		$baseString = 'test_base_string';
		$key = 'test_key';

		$signature = $signatureMethod->generateSignature($baseString, $key);

		$this->assertNotEmpty($signature, 'Unable to generate signature!');
	}

	/**
	 * @depends testGenerateSignature
	 */
	public function testVerify()
	{
		$signatureMethod = $this->createTestSignatureMethod();

		$baseString = 'test_base_string';
		$key = 'test_key';
		$signature = 'unsigned';
		$this->assertFalse($signatureMethod->verify($signature, $baseString, $key), 'Unsigned signature is valid!');

		$generatedSignature = $signatureMethod->generateSignature($baseString, $key);
		$this->assertTrue($signatureMethod->verify($generatedSignature, $baseString, $key), 'Generated signature is invalid!');
	}
}