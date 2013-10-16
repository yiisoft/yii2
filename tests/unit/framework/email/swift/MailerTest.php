<?php

namespace yiiunit\framework\email\swift;

use Yii;
use yii\email\swift\Mailer;
use yii\email\VendorMessage;
use yiiunit\TestCase;

class MailerTest extends TestCase
{
	public function setUp()
	{
		$this->mockApplication();
		Yii::$app->setComponent('email', $this->createTestEmailComponent());
	}

	/**
	 * @return Mailer test email component instance.
	 */
	protected function createTestEmailComponent()
	{
		$component = new Mailer();
		$component->init();
		return $component;
	}

	// Tests :

	public function testSend()
	{
		$emailAddress = 'someuser@somedomain.com';
		$message = new VendorMessage();
		$message->setTo($emailAddress);
		$message->setFrom($emailAddress);
		$message->setSubject('Yii Swift Test');
		$message->setBody('Yii Swift Test body', 'text/html');
		$message->send();
		$this->assertTrue(true);
	}
}