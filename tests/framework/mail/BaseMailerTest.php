<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\mail;

use Yii;
use yii\helpers\FileHelper;
use yiiunit\data\mail\TestMailer;
use yiiunit\data\mail\TestMessage;
use yiiunit\TestCase;

/**
 * @group mail
 */
class BaseMailerTest extends TestCase
{
    public function setUp()
    {
        $this->mockApplication([
            'components' => [
                'mailer' => $this->createTestMailComponent(),
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
     * @return TestMailer test email component instance.
     */
    protected function createTestMailComponent()
    {
        return new TestMailer([
            'composer' => [
                'viewPath' => $this->getTestFilePath()
            ]
        ]);
    }

    /**
     * @return TestMailer mailer instance
     */
    protected function getTestMailComponent()
    {
        return Yii::$app->get('mailer');
    }

    // Tests :

    public function testCreateMessage()
    {
        $mailer = new TestMailer();
        $message = $mailer->compose();
        $this->assertTrue(is_object($message), 'Unable to create message instance!');
        $this->assertEquals($mailer->messageClass, get_class($message), 'Invalid message class!');
    }

    /**
     * @depends testCreateMessage
     */
    public function testDefaultMessageConfig()
    {
        $mailer = new TestMailer();

        $notPropertyConfig = [
            'charset' => 'utf-16',
            'from' => 'from@domain.com',
            'to' => 'to@domain.com',
            'cc' => 'cc@domain.com',
            'bcc' => 'bcc@domain.com',
            'subject' => 'Test subject',
            'textBody' => 'Test text body',
            'htmlBody' => 'Test HTML body',
        ];
        $propertyConfig = [
            'id' => 'test-id',
            'encoding' => 'test-encoding',
        ];
        $messageConfig = array_merge($notPropertyConfig, $propertyConfig);
        $mailer->messageConfig = $messageConfig;

        $message = $mailer->compose();

        foreach ($notPropertyConfig as $name => $value) {
            $this->assertEquals($value, $message->{'_' . $name});
        }
        foreach ($propertyConfig as $name => $value) {
            $this->assertEquals($value, $message->$name);
        }
    }

    /**
     * @depends testCreateMessage
     */
    public function testCompose()
    {
        $mailer = $this->getTestMailComponent();
        $mailer->composer->htmlLayout = false;
        $mailer->composer->textLayout = false;

        $htmlViewName = 'test_html_view';
        $htmlViewFileName = $this->getTestFilePath() . DIRECTORY_SEPARATOR . $htmlViewName . '.php';
        $htmlViewFileContent = 'HTML <b>view file</b> content';
        file_put_contents($htmlViewFileName, $htmlViewFileContent);

        $textViewName = 'test_text_view';
        $textViewFileName = $this->getTestFilePath() . DIRECTORY_SEPARATOR . $textViewName . '.php';
        $textViewFileContent = 'Plain text view file content';
        file_put_contents($textViewFileName, $textViewFileContent);

        $message = $mailer->compose([
            'html' => $htmlViewName,
            'text' => $textViewName,
        ]);
        $this->assertEquals($htmlViewFileContent, $message->_htmlBody, 'Unable to render html!');
        $this->assertEquals($textViewFileContent, $message->_textBody, 'Unable to render text!');

        $message = $mailer->compose($htmlViewName);
        $this->assertEquals($htmlViewFileContent, $message->_htmlBody, 'Unable to render html by direct view!');
        $this->assertEquals(strip_tags($htmlViewFileContent), $message->_textBody, 'Unable to render text by direct view!');
    }

    public function testUseFileTransport()
    {
        $mailer = new TestMailer();
        $this->assertFalse($mailer->useFileTransport);
        $this->assertEquals('@runtime/mail', $mailer->fileTransportPath);

        $mailer->fileTransportPath = '@yiiunit/runtime/mail';
        $mailer->useFileTransport = true;
        $mailer->fileTransportCallback = function () {
            return 'message.txt';
        };
        $message = $mailer->compose()
            ->setTo('to@example.com')
            ->setFrom('from@example.com')
            ->setSubject('test subject')
            ->setTextBody('text body' . microtime(true));
        $this->assertTrue($mailer->send($message));
        $file = Yii::getAlias($mailer->fileTransportPath) . '/message.txt';
        $this->assertTrue(is_file($file));
        $this->assertEquals($message->toString(), file_get_contents($file));
    }

    public function testBeforeSendEvent()
    {
        $message = new TestMessage();

        $mailerMock = $this->getMockBuilder(TestMailer::class)->setMethods(['beforeSend', 'afterSend'])->getMock();
        $mailerMock->expects($this->once())->method('beforeSend')->with($message)->will($this->returnValue(true));
        $mailerMock->expects($this->once())->method('afterSend')->with($message, true);
        $mailerMock->send($message);
    }
}