<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use yii\base\InvalidConfigException;
use yii\log\EmailTarget;
use yii\mail\BaseMailer;
use yii\mail\BaseMessage;
use yiiunit\TestCase;

/**
 * Class EmailTargetTest.
 * @group log
 */
class EmailTargetTest extends TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $mailer;

    /**
     * Set up mailer.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->mailer = $this->createPartialMock(BaseMailer::class, ['compose', 'sendMessage']);
    }

    /**
     * @covers \yii\log\EmailTarget::init()
     */
    public function testInitWithOptionTo(): void
    {
        $target = new EmailTarget(['mailer' => $this->mailer, 'message' => ['to' => 'developer1@example.com']]);
        $this->assertIsObject($target); // should be no exception during `init()`
    }

    /**
     * @covers \yii\log\EmailTarget::init()
     */
    public function testInitWithoutOptionTo(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('The "to" option must be set for EmailTarget::message.');

        new EmailTarget(['mailer' => $this->mailer]);
    }

    /**
     * @covers \yii\log\EmailTarget::export()
     * @covers \yii\log\EmailTarget::composeMessage()
     */
    public function testExportWithSubject(): void
    {
        $message1 = ['A very looooooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 1'];
        $message2 = ['A very looooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 2'];
        $messages = [$message1, $message2];
        $textBody = wordwrap(implode("\n", [$message1[0], $message2[0]]), 70);

        $message = $this->getMockBuilder(BaseMessage::class)
            ->onlyMethods(['setTextBody', 'send', 'setSubject'])
            ->getMockForAbstractClass();
        $message->method('send')->willReturn(true);

        $this->mailer->expects($this->once())->method('compose')->willReturn($message);

        $message->expects($this->once())->method('setTextBody')->with($this->equalTo($textBody));
        $message->expects($this->once())->method('send')->with($this->equalTo($this->mailer));
        $message->expects($this->once())->method('setSubject')->with($this->equalTo('Hello world'));

        /** @var EmailTarget $mailTarget */
        $mailTarget = $this->getMockBuilder(EmailTarget::class)
            ->onlyMethods(['formatMessage'])
            ->setConstructorArgs([
                [
                    'mailer' => $this->mailer,
                    'message' => [
                        'to' => 'developer@example.com',
                        'subject' => 'Hello world',
                    ],
                ],
            ])
            ->getMock();

        $mailTarget->messages = $messages;
        $mailTarget->expects($this->exactly(2))->method('formatMessage')->willReturnMap(
            [
                [$message1, $message1[0]],
                [$message2, $message2[0]],
            ]
        );
        $mailTarget->export();
    }

    /**
     * @covers \yii\log\EmailTarget::export()
     * @covers \yii\log\EmailTarget::composeMessage()
     */
    public function testExportWithoutSubject(): void
    {
        $message1 = ['A veeeeery loooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 3'];
        $message2 = ['Message 4'];
        $messages = [$message1, $message2];
        $textBody = wordwrap(implode("\n", [$message1[0], $message2[0]]), 70);

        $message = $this->getMockBuilder(BaseMessage::class)
            ->onlyMethods(['setTextBody', 'send', 'setSubject'])
            ->getMockForAbstractClass();
        $message->method('send')->willReturn(true);

        $this->mailer->expects($this->once())->method('compose')->willReturn($message);

        $message->expects($this->once())->method('setTextBody')->with($this->equalTo($textBody));
        $message->expects($this->once())->method('send')->with($this->equalTo($this->mailer));
        $message->expects($this->once())->method('setSubject')->with($this->equalTo('Application Log'));

        /** @var EmailTarget $mailTarget */
        $mailTarget = $this->getMockBuilder(EmailTarget::class)
            ->onlyMethods(['formatMessage'])
            ->setConstructorArgs([
                [
                    'mailer' => $this->mailer,
                    'message' => [
                        'to' => 'developer@example.com',
                    ],
                ],
            ])
            ->getMock();

        $mailTarget->messages = $messages;
        $mailTarget->expects($this->exactly(2))->method('formatMessage')->willReturnMap(
            [
                [$message1, $message1[0]],
                [$message2, $message2[0]],
            ]
        );
        $mailTarget->export();
    }

    /**
     * @covers \yii\log\EmailTarget::export()
     *
     * See https://github.com/yiisoft/yii2/issues/14296
     */
    public function testExportWithSendFailure(): void
    {
        $message = $this->getMockBuilder(BaseMessage::class)
            ->onlyMethods(['send'])
            ->getMockForAbstractClass();
        $message->method('send')->willReturn(false);
        $this->mailer->expects($this->once())->method('compose')->willReturn($message);

        /** @var EmailTarget $mailTarget */
        $mailTarget = $this->getMockBuilder(EmailTarget::class)
            ->onlyMethods(['formatMessage'])
            ->setConstructorArgs([
                [
                    'mailer' => $this->mailer,
                    'message' => [
                        'to' => 'developer@example.com',
                    ],
                ],
            ])
            ->getMock();

        $this->expectException('yii\log\LogRuntimeException');
        $mailTarget->export();
    }
}
