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
		$message = $mailer->message();

		$viewName = 'test/text/view';
		$message->renderText($viewName);
		$expectedText = 'view=' . $viewName . ' layout=' . $mailer->textLayout;
		$this->assertEquals($expectedText, $message->text, 'Unable to render text!');

		$viewName = 'test/html/view';
		$message->renderHtml($viewName);
		$expectedHtml = 'view=' . $viewName . ' layout=' . $mailer->htmlLayout;
		$this->assertEquals($expectedHtml, $message->html, 'Unable to render html!');
	}

	/**
	 * @depends testRender
	 */
	public function testComposeBody()
	{
		$mailer = $this->getMailer();
		$message = $mailer->message();

		$viewName = 'test/html/view';
		$message->renderBody($viewName);
		$expectedHtml = 'view=' . $viewName . ' layout=' . $mailer->htmlLayout;
		$this->assertEquals($expectedHtml, $message->html, 'Unable to compose html!');
		$expectedText = strip_tags($expectedHtml);
		$this->assertEquals($expectedText, $message->text, 'Unable to compose text from html!');

		$textViewName = 'test/text/view';
		$htmlViewName = 'test/html/view';
		$message->renderBody(['text' => $textViewName, 'html' => $htmlViewName]);
		$expectedHtml = 'view=' . $htmlViewName . ' layout=' . $mailer->htmlLayout;
		$this->assertEquals($expectedHtml, $message->html, 'Unable to compose html from separated view!');
		$expectedText = 'view=' . $textViewName . ' layout=' . $mailer->textLayout;
		$this->assertEquals($expectedText, $message->text, 'Unable to compose text from separated view!');
	}

	public function testSend()
	{
		$mailer = $this->getMailer();
		$message = $mailer->message();
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