<?php

namespace yiiunit\extensions\authclient\oauth\signature;

use yii\authclient\signature\PlainText;
use yiiunit\extensions\authclient\TestCase;

class PlainTextTest extends TestCase
{
	public function testGenerateSignature()
	{
		$signatureMethod = new PlainText();

		$baseString = 'test_base_string';
		$key = 'test_key';

		$signature = $signatureMethod->generateSignature($baseString, $key);
		$this->assertNotEmpty($signature, 'Unable to generate signature!');
	}
}
