<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use yii\log\EmailTarget;
use yiiunit\TestCase;

/**
 * Class EmailTargetTest.
 * @group log
 */
class EmailTargetTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\yii\mail\BaseMailer
     */
    protected $mailer;

    /**
     * Set up mailer.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->mailer = $this->getMockBuilder('yii\\mail\\BaseMailer')
            ->setMethods(['compose'])
            ->getMockForAbstractClass();
    }

    /**
     * @covers \yii\log\EmailTarget::init()
     */
    public function testInitWithOptionTo()
    {
        $target = new EmailTarget(['mailer' => $this->mailer, 'message' => ['to' => 'developer1@example.com']]);
        $this->assertInternalType('object', $target); // should be no exception during `init()`
    }

    /**
     * @covers \yii\log\EmailTarget::init()
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage The "to" option must be set for EmailTarget::message.
     */
    public function testInitWithoutOptionTo()
    {
        new EmailTarget(['mailer' => $this->mailer]);
    }

    /**
     * @covers \yii\log\EmailTarget::export()
     * @covers \yii\log\EmailTarget::composeMessage()
     */
    public function testExportWithSubject()
    {
        $message1 = ['A very looooooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 1'];
        $message2 = ['A very looooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 2'];
        $messages = [$message1, $message2];
        $textBody = wordwrap(implode("\n", [$message1[0], $message2[0]]), 70);

        $message = $this->getMockBuilder('yii\\mail\\BaseMessage')
            ->setMethods(['setTextBody', 'send', 'setSubject'])
            ->getMockForAbstractClass();
        $message->method('send')->willReturn(true);

        $this->mailer->expects($this->once())->method('compose')->willReturn($message);

        $message->expects($this->once())->method('setTextBody')->with($this->equalTo($textBody));
        $message->expects($this->once())->method('send')->with($this->equalTo($this->mailer));
        $message->expects($this->once())->method('setSubject')->with($this->equalTo('Hello world'));

        $mailTarget = $this->getMockBuilder('yii\\log\\EmailTarget')
            ->setMethods(['formatMessage'])
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
    public function testExportWithoutSubject()
    {
        $message1 = ['A veeeeery loooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 3'];
        $message2 = ['Message 4'];
        $messages = [$message1, $message2];
        $textBody = wordwrap(implode("\n", [$message1[0], $message2[0]]), 70);

        $message = $this->getMockBuilder('yii\\mail\\BaseMessage')
            ->setMethods(['setTextBody', 'send', 'setSubject'])
            ->getMockForAbstractClass();
        $message->method('send')->willReturn(true);

        $this->mailer->expects($this->once())->method('compose')->willReturn($message);

        $message->expects($this->once())->method('setTextBody')->with($this->equalTo($textBody));
        $message->expects($this->once())->method('send')->with($this->equalTo($this->mailer));
        $message->expects($this->once())->method('setSubject')->with($this->equalTo('Application Log'));

        $mailTarget = $this->getMockBuilder('yii\\log\\EmailTarget')
            ->setMethods(['formatMessage'])
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
    public function testExportWithSendFailure()
    {
        $message = $this->getMockBuilder('yii\\mail\\BaseMessage')
            ->setMethods(['send'])
            ->getMockForAbstractClass();
        $message->method('send')->willReturn(false);
        $this->mailer->expects($this->once())->method('compose')->willReturn($message);
        $mailTarget = $this->getMockBuilder('yii\\log\\EmailTarget')
            ->setMethods(['formatMessage'])
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
