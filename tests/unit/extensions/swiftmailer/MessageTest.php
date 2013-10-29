<?php

namespace yiiunit\extensions\swiftmailer;

use Yii;
use yii\swiftmailer\Mailer;
use yii\swiftmailer\Message;
use yiiunit\VendorTestCase;

/**
 * @group vendor
 * @group mail
 * @group swiftmailer
 */
class MessageTest extends VendorTestCase
{
	/**
	 * @var string test email address, which will be used as receiver for the messages.
	 */
	protected $testEmailReceiver = 'someuser@somedomain.com';

	public function setUp()
	{
		$this->mockApplication([
			'components' => [
				'mail' => $this->createTestEmailComponent()
			]
		]);
	}

	/**
	 * @return Mailer test email component instance.
	 */
	protected function createTestEmailComponent()
	{
		$component = new Mailer();
		return $component;
	}

	/**
	 * @return Message test message instance.
	 */
	protected function createTestMessage()
	{
		return Yii::$app->getComponent('mail')->createMessage();
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
		$message = $this->createTestMessage();
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
		$message = $this->createTestMessage();
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
		$message = $this->createTestMessage();
		$message->setTo($this->testEmailReceiver);
		$message->setFrom('someuser@somedomain.com');
		$message->setSubject('Yii Swift Create Attachment Test');
		$message->setText('Yii Swift Create Attachment Test body');
		$message->attachContentAsFile('Test attachment content', 'test.txt');
		$this->assertTrue($message->send());
	}

	/**
	 * @depends testSend
	 */
	public function testSendAlternativeBody()
	{
		$message = $this->createTestMessage();
		$message->setTo($this->testEmailReceiver);
		$message->setFrom('someuser@somedomain.com');
		$message->setSubject('Yii Swift Alternative Body Test');
		$message->addHtml('<b>Yii Swift</b> test HTML body');
		$message->addText('Yii Swift test plain text body');
		$this->assertTrue($message->send());
	}
}