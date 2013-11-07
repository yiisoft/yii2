<?php

namespace yiiunit\framework\mail;

use Yii;
use yii\mail\BaseMailer;
use yii\mail\BaseMessage;
use yiiunit\TestCase;

/**
 * @group mail
 */
class BaseMessageTest extends TestCase
{
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
		$component = new TestMailer();
		return $component;
	}

	/**
	 * @return TestMailer mailer instance.
	 */
	protected function getMailer()
	{
		return Yii::$app->getComponent('mail');
	}

	// Tests :

	public function testGetMailer()
	{
		$mailer = $this->getMailer();
		$message = $mailer->compose();
		$this->assertEquals($mailer, $message->getMailer());
	}

	public function testSend()
	{
		$mailer = $this->getMailer();
		$message = $mailer->compose();
		$message->send();
		$this->assertEquals($message, $mailer->sentMessages[0], 'Unable to send message!');
	}

	public function testToString()
	{
		$mailer = $this->getMailer();
		$message = $mailer->compose();
		$this->assertEquals($message->toString(), '' . $message);
	}
}

/**
 * Test Mailer class
 */
class TestMailer extends BaseMailer
{
	public $messageClass = 'yiiunit\framework\mail\TestMessage';
	public $sentMessages = array();

	public function send($message)
	{
		$this->sentMessages[] = $message;
	}
}

/**
 * Test Message class
 */
class TestMessage extends BaseMessage
{
	public $text;
	public $html;

	public function charset($charset) {}

	public function from($from) {}

	public function to($to) {}

	public function cc($cc) {}

	public function bcc($bcc) {}

	public function subject($subject) {}

	public function text($text) {
		$this->text = $text;
	}

	public function html($html) {
		$this->html = $html;
	}

	public function attachContent($content, array $options = []) {}

	public function attach($fileName, array $options = []) {}

	public function embed($fileName, array $options = []) {}

	public function embedContent($content, array $options = []) {}

	public function toString()
	{
		return get_class($this);
	}
}