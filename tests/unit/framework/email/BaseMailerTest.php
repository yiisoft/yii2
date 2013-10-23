<?php

namespace yiiunit\framework\email;

use Yii;
use yii\base\View;
use yii\email\BaseMailer;
use yii\email\BaseMessage;
use yiiunit\TestCase;

/**
 * @group email
 */
class BaseMailerTest extends TestCase
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
		return $component;
	}

	// Tests :

	public function testSetupView()
	{
		$mailer = new Mailer();

		$view = new View();
		$mailer->setView($view);
		$this->assertEquals($view, $mailer->getView(), 'Unable to setup view!');
	}

	public function testGetDefaultView()
	{
		$mailer = new Mailer();
		$view = $mailer->getView();
		$this->assertTrue(is_object($view), 'Unable to get default view!');
	}

	public function testDefaultMessageConfig()
	{
		$defaultMessageConfig = array(
			'id' => 'test-id',
			'encoding' => 'test-encoding',
		);
		Yii::$app->getComponent('email')->setDefaultMessageConfig($defaultMessageConfig);

		$message = new Message();

		foreach ($defaultMessageConfig as $name => $value) {
			$this->assertEquals($value, $message->$name);
		}
	}
}

/**
 * Test Mailer class
 */
class Mailer extends BaseMailer
{
	public $sentMessages = array();

	public function send($message)
	{
		$this->sentMessages[] = $message;
	}
}

/**
 * Test Message class
 */
class Message extends BaseMessage
{
	public $id;
	public $encoding;

	public function setFrom($from) {}

	public function setTo($to) {}

	public function setSubject($subject) {}

	public function setText($text) {}

	public function setHtml($html) {}

	public function addText($text) {}

	public function addHtml($html) {}

	public function createAttachment($content, $fileName, $contentType = 'application/octet-stream') {}
}