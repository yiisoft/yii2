<?php

namespace yiiunit\framework\email;

use yii\email\VendorMailer;
use yii\test\TestCase;

class VendorMailerTest extends TestCase
{
	/**
	 * @return VendorMailer test mailer instance
	 */
	protected function createTestMailer()
	{
		$mailer = $this->getMock('yii\email\VendorMailer', array('createVendorMailer', 'createVendorMessage', 'send'));
		$mailer->expects($this->any())->method('createVendorMailer')->will($this->returnValue(new \stdClass()));
		return $mailer;
	}

	// Tests :

	public function testSetupVendorMailer()
	{
		$mailer = $this->createTestMailer();
		$vendorMailer = new \stdClass();
		$mailer->setVendorMailer($vendorMailer);
		$this->assertEquals($vendorMailer, $mailer->getVendorMailer(), 'Unable to setup vendor mailer!');
	}

	/**
	 * @depends testSetupVendorMailer
	 */
	public function testGetDefaultVendorMailer()
	{
		$mailer = $this->createTestMailer();
		$vendorMailer = $mailer->getVendorMailer();
		$this->assertTrue(is_object($vendorMailer), 'Unable to get default vendor mailer!');
	}
}