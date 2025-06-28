<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

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
    protected function setUp(): void
    {
        $this->mockApplication([
            'components' => [
                'mailer' => $this->createTestEmailComponent(),
            ],
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
        return Yii::$app->get('mailer');
    }

    // Tests :

    public function testSend(): void
    {
        $mailer = $this->getMailer();
        $message = $mailer->compose();
        $message->send($mailer);
        $this->assertEquals($message, $mailer->sentMessages[0], 'Unable to send message!');
    }

    public function testToString(): void
    {
        $mailer = $this->getMailer();
        $message = $mailer->compose();
        $this->assertEquals($message->toString(), '' . $message);
    }

    public function testExceptionToString()
    {
        if (PHP_VERSION_ID < 70400) {
            $this->markTestSkipped('This test is for PHP 7.4+ only');
        }

        $message = new TestMessageWithException();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test exception in toString.');

        (string) $message;
    }

    public function testExceptionToStringLegacy()
    {
        if (PHP_VERSION_ID >= 70400) {
            $this->markTestSkipped('This test is for PHP < 7.4 only');
        }

        $message = new TestMessageWithException();

        $errorTriggered = false;
        $errorMessage = '';

        set_error_handler(
            function ($severity, $message, $file, $line) use (&$errorTriggered, &$errorMessage) {
                if ($severity === E_USER_ERROR) {
                    $errorTriggered = true;
                    $errorMessage = $message;

                    return true;
                }

                return false;
            },
            E_USER_ERROR,
        );

        $result = (string) $message;

        restore_error_handler();

        $this->assertTrue($errorTriggered, 'E_USER_ERROR should have been triggered');
        $this->assertStringContainsString('Test exception in toString.', $errorMessage);
        $this->assertSame('', $result, 'Result should be an empty string');
    }
}

/**
 * Test Mailer class.
 */
class TestMailer extends BaseMailer
{
    public $messageClass = 'yiiunit\framework\mail\TestMessage';
    public $sentMessages = [];

    protected function sendMessage($message)
    {
        $this->sentMessages[] = $message;
    }
}

/**
 * Test Message class.
 */
class TestMessage extends BaseMessage
{
    public $text;
    public $html;

    public function getCharset()
    {
        return '';
    }

    public function setCharset($charset): void
    {
    }

    public function getFrom()
    {
        return '';
    }

    public function setFrom($from): void
    {
    }

    public function getReplyTo()
    {
        return '';
    }

    public function setReplyTo($replyTo): void
    {
    }

    public function getTo()
    {
        return '';
    }

    public function setTo($to): void
    {
    }

    public function getCc()
    {
        return '';
    }

    public function setCc($cc): void
    {
    }

    public function getBcc()
    {
        return '';
    }

    public function setBcc($bcc): void
    {
    }

    public function getSubject()
    {
        return '';
    }

    public function setSubject($subject): void
    {
    }

    public function setTextBody($text): void
    {
        $this->text = $text;
    }

    public function setHtmlBody($html): void
    {
        $this->html = $html;
    }

    public function attachContent($content, array $options = []): void
    {
    }

    public function attach($fileName, array $options = []): void
    {
    }

    public function embed($fileName, array $options = []): void
    {
    }

    public function embedContent($content, array $options = []): void
    {
    }

    public function toString()
    {
        return static::class;
    }
}

class TestMessageWithException extends TestMessage
{
    public function toString()
    {
        throw new \Exception('Test exception in toString.');
    }
}
