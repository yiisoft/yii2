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
        $message = new TestMessageWithException();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception in toString.');

        (string) $message;
    }
}
