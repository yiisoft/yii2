<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use yii\base\InvalidConfigException;
use yii\log\{EmailTarget, Logger};
use yii\mail\BaseMailer;
use yiiunit\data\log\EmailMessageStub;
use yiiunit\framework\log\providers\EmailTargetProvider;
use yiiunit\TestCase;

/**
 * Unit tests for {@see EmailTarget}.
 */
#[Group('log')]
final class EmailTargetTest extends TestCase
{
    /**
     * @var BaseMailer&MockObject
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

    public function testInitWithOptionTo(): void
    {
        $target = new EmailTarget(
            [
                'mailer' => $this->mailer,
                'message' => ['to' => 'developer1@example.com'],
            ],
        );

        self::assertInstanceOf(
            EmailTarget::class,
            $target,
            'A configured recipient must allow target initialization.',
        );
    }

    public function testInitWithoutOptionTo(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            'The "to" option must be set for EmailTarget::message.',
        );

        new EmailTarget(['mailer' => $this->mailer]);
    }

    #[DataProviderExternal(EmailTargetProvider::class, 'subjects')]
    public function testExport(?string $subject, string $expectedSubject): void
    {
        $message1 = [
            'A very looooooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 1',
            Logger::LEVEL_INFO,
            'application',
            0.0,
            [],
        ];
        $message2 = [
            'A very looooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 2',
            Logger::LEVEL_INFO,
            'application',
            0.0,
            [],
        ];
        $messages = [$message1, $message2];

        $textBody = wordwrap(implode("\n", [$message1[0], $message2[0]]), 70);

        $message = $this->getMockBuilder(EmailMessageStub::class)
            ->onlyMethods(['setTextBody', 'send', 'setSubject'])
            ->getMock();

        $message
            ->method('send')
            ->willReturn(true);

        $this->mailer
            ->expects($this->once())
            ->method('compose')
            ->willReturn($message);

        $message
            ->expects($this->once())
            ->method('setTextBody')
            ->with($this->equalTo($textBody));
        $message
            ->expects($this->once())
            ->method('send')
            ->with($this->equalTo($this->mailer));
        $message
            ->expects($this->once())
            ->method('setSubject')
            ->with($this->equalTo($expectedSubject));

        $messageConfig = ['to' => 'developer@example.com'];

        if ($subject !== null) {
            $messageConfig['subject'] = $subject;
        }

        $mailTarget = $this->getMockBuilder(EmailTarget::class)
            ->onlyMethods(['formatMessage'])
            ->setConstructorArgs([
                [
                    'mailer' => $this->mailer,
                    'message' => $messageConfig,
                ],
            ])
            ->getMock();

        $mailTarget->messages = $messages;

        $mailTarget
            ->expects($this->exactly(2))
            ->method('formatMessage')
            ->willReturnMap(
                [
                    [$message1, $message1[0]],
                    [$message2, $message2[0]],
                ],
            );

        $mailTarget->export();
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/14296
     */
    public function testExportWithSendFailure(): void
    {
        $message = $this->getMockBuilder(EmailMessageStub::class)
            ->onlyMethods(['setTextBody', 'send', 'setSubject'])
            ->getMock();

        $message
            ->method('send')
            ->willReturn(false);

        $this->mailer
            ->expects($this->once())
            ->method('compose')
            ->willReturn($message);

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
