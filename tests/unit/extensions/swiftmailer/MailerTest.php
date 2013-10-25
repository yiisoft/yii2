<?php

namespace yiiunit\extensions\swiftmailer;

use Yii;
use yii\swiftmailer\Mailer;
use yii\swiftmailer\Message;
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
		return $component;
	}

	// Tests :

	public function testSetupTransport()
	{
		$mailer = new Mailer();

		$transport = \Swift_MailTransport::newInstance();
		$mailer->setTransport($transport);
		$this->assertEquals($transport, $mailer->getTransport(), 'Unable to setup transport!');
	}

	/**
	 * @depends testSetupTransport
	 */
	public function testConfigureTransport()
	{
		$mailer = new Mailer();

		$transportConfig = [
			'class' => 'Swift_SmtpTransport',
			'host' => 'localhost',
		];
		$mailer->setTransport($transportConfig);
		$transport = $mailer->getTransport();
		$this->assertTrue(is_object($transport), 'Unable to setup transport via config!');
		$this->assertEquals($transportConfig['class'], get_class($transport), 'Invalid transport class!');
		$this->assertEquals($transportConfig['host'], $transport->getHost(), 'Invalid transport host!');
	}

	public function testGetSwiftMailer()
	{
		$mailer = new Mailer();
		$this->assertTrue(is_object($mailer->getSwiftMailer()), 'Unable to get Swift mailer instance!');
	}

	public function testCreateSwiftMessage()
	{
		$mailer = new Mailer();
		$message = $mailer->createSwiftMessage();
		$this->assertTrue(is_object($message), 'Unable to create Swift message instance!');
	}
}