<?php

namespace yiiunit\framework\email;

use Yii;
use yii\email\VendorMailer;
use yii\email\VendorMessage;
use yiiunit\TestCase;

class VendorMessageTest extends TestCase
{
	public function setUp()
	{
		$this->mockApplication();
		Yii::$app->setComponent('email', $this->createTestEmailComponent());
	}

	/**
	 * @return TestMailer test email component instance.
	 */
	protected function createTestEmailComponent()
	{
		$component = new TestMailer();
		return $component;
	}

	// Tests :

	public function testGetVendorMessage()
	{
		$message = new VendorMessage();
		$vendorMessage = $message->getVendorMessage();
		$this->assertTrue(is_object($vendorMessage), 'Unable to get vendor message!');
	}

	/**
	 * @depends testGetVendorMessage
	 */
	public function testVendorMethodCall()
	{
		$message = new VendorMessage();
		$result = $message->composeString();
		$this->assertNotEmpty($result, 'Unable to call method of vendor message!');
	}

	/**
	 * @depends testGetVendorMessage
	 */
	public function testVendorPropertyAccess()
	{
		$message = new VendorMessage();

		$value = 'test public field value';
		$message->publicField = $value;
		$this->assertEquals($value, $message->publicField, 'Unable to access public property!');
		$this->assertTrue(isset($message->publicField), 'Unable to check if public property is set!');
		unset($message->publicField);
		$this->assertFalse(isset($message->publicField), 'Unable to unset the public property!');

		$value = 'test private field value';
		$message->privateField = $value;
		$this->assertEquals($value, $message->privateField, 'Unable to access virtual property!');
		$this->assertTrue(isset($message->privateField), 'Unable to check if private property is set!');
		unset($message->privateField);
		$this->assertFalse(isset($message->privateField), 'Unable to unset the private property!');

	}
}

class TestVendorMessage
{
	public $publicField;
	private $_privateField;

	public function setPrivateField($value)
	{
		$this->_privateField = $value;
	}

	public function getPrivateField()
	{
		return $this->_privateField;
	}

	public function composeString()
	{
		return get_class($this);
	}
}

class TestMailer extends VendorMailer
{
	public $sentMessages = array();

	public function send($message)
	{
		$this->sentMessages[] = $message;
	}

	protected function createVendorMailer(array $config)
	{
		return new \stdClass();
	}

	public function createVendorMessage()
	{
		return new TestVendorMessage();
	}
}