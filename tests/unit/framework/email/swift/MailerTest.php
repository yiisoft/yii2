<?php

namespace yiiunit\framework\email\swift;

use Yii;
use yii\email\swift\Mailer;
use yii\email\swift\Message;
use yiiunit\TestCase;

/**
 * @group email
 * @group swiftmailer
 */
class MailerTest extends TestCase
{
	public function setUp()
	{
		$this->mockApplication(array(
			'vendorPath' => Yii::getAlias('@yiiunit/vendor')
		));
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
		$message = new Message();
		$message->setTo($emailAddress);
		$message->setFrom($emailAddress);
		$message->setSubject('Yii Swift Test');
		$message->setText('Yii Swift Test body');
		$message->send();
		$this->assertTrue(true);
	}
}