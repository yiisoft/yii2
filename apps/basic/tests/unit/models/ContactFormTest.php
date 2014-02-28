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
		Yii::$app->mail->fileTransportCallback = function ($mailer, $message) {
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
			expect('email file should exist', file_exists($this->getMessageFile()))->true();
		});

		$this->specify('message should contain correct data', function () use ($model) {
			$emailMessage = file_get_contents($this->getMessageFile());

			expect('email should contain user name', $emailMessage)->contains($model->name);
			expect('email should contain sender email', $emailMessage)->contains($model->email);
			expect('email should contain subject', $emailMessage)->contains($model->subject);
			expect('email should contain body', $emailMessage)->contains($model->body);
		});
	}

	private function getMessageFile()
	{
		return Yii::getAlias(Yii::$app->mail->fileTransportPath) . '/testing_message.eml';
	}
}
