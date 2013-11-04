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

	public function testRender()
	{
		$mailer = $this->getMailer();
		$message = $mailer->createMessage();

		$viewName = 'test/text/view';
		$message->renderText($viewName);
		$expectedText = 'view=' . $viewName . ' layout=' . $mailer->textLayout;
		$this->assertEquals($expectedText, $message->text, 'Unable to render text!');

		$viewName = 'test/html/view';
		$message->renderHtml($viewName);
		$expectedHtml = 'view=' . $viewName . ' layout=' . $mailer->htmlLayout;
		$this->assertEquals($expectedHtml, $message->html, 'Unable to render text!');
	}

	public function testSend()
	{
		$mailer = $this->getMailer();
		$message = $mailer->createMessage();
		$message->send();
		$this->assertEquals($message, $mailer->sentMessages[0], 'Unable to send message!');
	}
}

/**
 * Test Mailer class
 */
class TestMailer extends BaseMailer
{
	public $messageClass = 'yiiunit\framework\mail\TestMessage';
	public $sentMessages = array();

	public function render($view, $params = [], $layout = false)
	{
		return 'view=' . $view . ' layout=' . $layout;
	}

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

	public function setCharset($charset) {}

	public function setFrom($from) {}

	public function setTo($to) {}

	public function setCc($cc) {}

	public function setBcc($bcc) {}

	public function setSubject($subject) {}

	public function setText($text) {
		$this->text = $text;
	}

	public function setHtml($html) {
		$this->html = $html;
	}

	public function attachContent($content, array $options = []) {}

	public function attachFile($fileName, array $options = []) {}

	public function embedFile($fileName, array $options = []) {}

	public function embedContent($content, array $options = []) {}

	public function __toString()
	{
		return get_class($this);
	}
}