<?php

namespace yiiunit\extensions\authclient\oauth\signature;

use yii\authclient\oauth\signature\HmacSha1;
use yiiunit\extensions\authclient\TestCase;

class HmacSha1Test extends TestCase
{
	public function testGenerateSignature()
	{
		$signatureMethod = new HmacSha1();

		$baseString = 'test_base_string';
		$key = 'test_key';

		$signature = $signatureMethod->generateSignature($baseString, $key);
		$this->assertNotEmpty($signature, 'Unable to generate signature!');
	}
}