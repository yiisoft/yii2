<?php

namespace yiiunit\extensions\swiftmailer;

use Yii;
use yii\helpers\FileHelper;
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
		$filePath = $this->getTestFilePath();
		if (!file_exists($filePath)) {
			FileHelper::createDirectory($filePath);
		}
	}

	public function tearDown()
	{
		$filePath = $this->getTestFilePath();
		if (file_exists($filePath)) {
			FileHelper::removeDirectory($filePath);
		}
	}

	/**
	 * @return string test file path.
	 */
	protected function getTestFilePath()
	{
		return Yii::getAlias('@yiiunit/runtime') . DIRECTORY_SEPARATOR . basename(get_class($this)) . '_' . getmypid();
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

	/**
	 * Creates image file with given text.
	 * @param string $fileName file name.
	 * @param string $text text to be applied on image.
	 * @return string image file full name.
	 */
	protected function createImageFile($fileName = 'test.jpg', $text = 'Test Image')
	{
		if (!function_exists('imagecreatetruecolor')) {
			$this->markTestSkipped('GD lib required.');
		}
		$fileFullName = $this->getTestFilePath() . DIRECTORY_SEPARATOR . $fileName;
		$image = imagecreatetruecolor(120, 20);
		$textColor = imagecolorallocate($image, 233, 14, 91);
		imagestring($image, 1, 5, 5, $text, $textColor);
		imagejpeg($image, $fileFullName);
		imagedestroy($image);
		return $fileFullName;
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
	public function testAttachContent()
	{
		$message = $this->createTestMessage();
		$message->setTo($this->testEmailReceiver);
		$message->setFrom('someuser@somedomain.com');
		$message->setSubject('Yii Swift Create Attachment Test');
		$message->setText('Yii Swift Create Attachment Test body');
		$message->attachContent('Test attachment content', ['fileName' => 'test.txt']);
		$this->assertTrue($message->send());
	}

	/**
	 * @depends testSend
	 */
	public function testEmbedFile()
	{
		$fileName = $this->createImageFile('embed_file.jpg', 'Embed Image File');

		$message = $this->createTestMessage();

		$cid = $message->embedFile($fileName);

		$message->setTo($this->testEmailReceiver);
		$message->setFrom('someuser@somedomain.com');
		$message->setSubject('Yii Swift Embed File Test');
		$message->setHtml('Embed image: <img src="' . $cid. '" alt="pic">');

		$this->assertTrue($message->send());
	}

	/**
	 * @depends testSend
	 */
	public function testEmbedContent()
	{
		$fileName = $this->createImageFile('embed_file.jpg', 'Embed Image File');

		$message = $this->createTestMessage();

		$cid = $message->embedContent(file_get_contents($fileName), ['contentType' => 'image/jpeg']);

		$message->setTo($this->testEmailReceiver);
		$message->setFrom('someuser@somedomain.com');
		$message->setSubject('Yii Swift Embed File Test');
		$message->setHtml('Embed image: <img src="' . $cid. '" alt="pic">');

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