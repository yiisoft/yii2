<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\mail;

use Exception;
use Yii;
use yiiunit\framework\mail\stubs\TestMailer;
use yiiunit\framework\mail\stubs\TestMessage;
use yiiunit\framework\mail\stubs\TestMessageWithException;
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
     * @return TestMailer test email component instance.
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
        /** @var TestMessage $message */
        $message = $mailer->compose();
        $this->assertEquals($message->toString(), '' . $message);
    }

    public function testExceptionToString(): void
    {
        if (PHP_VERSION_ID < 70400) {
            $this->markTestSkipped('This test is for PHP 7.4+ only');
        }

        $message = new TestMessageWithException();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception in toString.');

        (string) $message;
    }

    public function testExceptionToStringLegacy(): void
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

