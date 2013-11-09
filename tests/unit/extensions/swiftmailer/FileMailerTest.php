<?php

namespace yiiunit\extensions\swiftmailer;

require __DIR__ . '/../../../../extensions/swiftmailer/FileMailer.php';

use yii\swiftmailer\FileMailer;
use yiiunit\VendorTestCase;

/**
 * @group vendor
 * @group mail
 * @group swiftmailer
 */
class FileMailerTest extends VendorTestCase
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
	 * @return FileMailer test email component instance.
	 */
	protected function createTestEmailComponent()
	{
		$component = new FileMailer([
			'callback' => function () {
				return 'Message_test.txt';
			}
		]);

		return $component;
	}

	public function testConfigurePath()
	{
		$mailer = new FileMailer();
		$this->assertEquals(\Yii::getAlias('@app/runtime/mail'), $mailer->getPath());
		$mailer->setPath('@yiiunit/runtime/');
		$this->assertEquals(\Yii::getAlias('@yiiunit/runtime'), $mailer->getPath());
	}

	public function testSend()
	{
		$message = \Yii::$app->mail->compose()
			->setTo('tester@example.com')
			->setFrom('admin@example.com')
			->setSubject('Just a test')
			->setHtmlBody('This is html body');
		$message->send();
		$this->assertEquals(
			$message->toString(),
			file_get_contents(\Yii::$app->getRuntimePath() . '/mail/Message_test.txt')
		);
	}
}
 