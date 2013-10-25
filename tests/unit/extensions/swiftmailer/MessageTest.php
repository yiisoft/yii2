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
class MessageTest extends TestCase
{
	/**
	 * @var string test email address, which will be used as receiver for the messages.
	 */
	protected $testEmailReceiver = 'someuser@somedomain.com';

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

	public function testGetSwiftMessage()
	{
		$message = new Message();
		$this->assertTrue(is_object($message->getSwiftMessage()), 'Unable to get Swift message!');
	}

	/**
	 * @depends testGetSwiftMessage
	 */
	public function testSend()
	{
		$message = new Message();
		$message->setTo($this->testEmailReceiver);
		$message->setFrom('someuser@somedomain.com');
		$message->setSubject('Yii Swift Test');
		$message->setText('Yii Swift Test body');
		$this->assertTrue($message->send());
	}

	/**
	 * @depends testSend
	 */
	public function testAttachFile()
	{
		$message = new Message();
		$message->setTo($this->testEmailReceiver);
		$message->setFrom('someuser@somedomain.com');
		$message->setSubject('Yii Swift Attach File Test');
		$message->setText('Yii Swift Attach File Test body');
		$message->attachFile(__FILE__);
		$this->assertTrue($message->send());
	}

	/**
	 * @depends testSend
	 */
	public function testCreateAttachment()
	{
		$message = new Message();
		$message->setTo($this->testEmailReceiver);
		$message->setFrom('someuser@somedomain.com');
		$message->setSubject('Yii Swift Create Attachment Test');
		$message->setText('Yii Swift Create Attachment Test body');
		$message->createAttachment('Test attachment content', 'test.txt');
		$this->assertTrue($message->send());
	}

	/**
	 * @depends testSend
	 */
	public function testSendAlternativeBody()
	{
		$message = new Message();
		$message->setTo($this->testEmailReceiver);
		$message->setFrom('someuser@somedomain.com');
		$message->setSubject('Yii Swift Alternative Body Test');
		$message->addHtml('<b>Yii Swift</b> test HTML body');
		$message->addText('Yii Swift test plain text body');
		$this->assertTrue($message->send());
	}
}