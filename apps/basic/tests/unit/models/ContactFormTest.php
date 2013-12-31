<?php

namespace tests\unit\models;

use Yii;
use yii\codeception\TestCase;

class ContactFormTest extends TestCase
{

	use \Codeception\Specify;

	protected function setUp()
	{
		parent::setUp();
		Yii::$app->mail->fileTransportCallback = function ($mailer, $message)
		{
			return 'testing_message.eml';
		};
	}

	protected function tearDown()
	{
		unlink($this->getMessageFile());
		parent::tearDown();
	}

	public function testContact()
	{
		$model = $this->getMock('app\models\ContactForm', ['validate']);
		$model->expects($this->once())->method('validate')->will($this->returnValue(true));

		$model->attributes = [
			'name' => 'Tester',
			'email' => 'tester@example.com',
			'subject' => 'very important letter subject',
			'body' => 'body of current message',
		];

		$model->contact('admin@example.com');

		$this->specify('email should be send', function () {
			$this->assertFileExists($this->getMessageFile(), 'email file should exist');
		});

		$this->specify('message should contain correct data', function () use($model) {
			$emailMessage = file_get_contents($this->getMessageFile());
			$this->assertContains($model->name, $emailMessage, 'email should contain user name');
			$this->assertContains($model->email, $emailMessage, 'email should contain sender email');
			$this->assertContains($model->subject, $emailMessage, 'email should contain subject');
			$this->assertContains($model->body, $emailMessage, 'email should contain body');
		});
	}

	private function getMessageFile()
	{
		return Yii::getAlias(Yii::$app->mail->fileTransportPath) . '/testing_message.eml';
	}

}
