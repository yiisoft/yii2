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
use Yii;
use yii\helpers\FileHelper;
use yii\log\Dispatcher;
use yii\log\FileTarget;
use yii\log\Logger;
use yiiunit\framework\log\mocks\CustomLogger;
use yiiunit\framework\log\providers\FileTargetProvider;
use yiiunit\TestCase;

/**
 * Unit tests for {@see FileTarget}.
 */
#[Group('log')]
final class FileTargetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication();
    }

    /**
     * Tests that log directory isn't created during init process
     * @see https://github.com/yiisoft/yii2/issues/15662
     */
    public function testInit(): void
    {
        $logFile = Yii::getAlias('@yiiunit/runtime/log/filetargettest.log');

        FileHelper::removeDirectory(dirname((string) $logFile));

        new FileTarget(
            [
                'logFile' => Yii::getAlias('@yiiunit/runtime/log/filetargettest.log'),
            ],
        );

        self::assertFileDoesNotExist(
            dirname((string) $logFile),
            'Log directory must not be created during initialization.',
        );
    }

    public function testRotate(): void
    {
        $logFile = Yii::getAlias('@yiiunit/runtime/log/filetargettest.log');

        FileHelper::removeDirectory(dirname((string) $logFile));

        mkdir(dirname((string) $logFile), 0777, true);

        $logger = new Logger();

        new Dispatcher(
            [
                'logger' => $logger,
                'targets' => [
                    'file' => [
                        'class' => 'yii\log\FileTarget',
                        'logFile' => $logFile,
                        'levels' => ['warning'],
                        'maxFileSize' => 1024, // 1 MB
                        'maxLogFiles' => 1, // one file for rotation and one normal log file
                        'logVars' => [],
                    ],
                ],
            ],
        );

        // one file

        $logger->log(str_repeat('x', 1024), Logger::LEVEL_WARNING);
        $logger->flush(true);

        clearstatcache();

        self::assertLogFileState(
            $logFile,
            false,
        );

        // exceed max size
        for ($i = 0; $i < 1024; $i++) {
            $logger->log(str_repeat('x', 1024), Logger::LEVEL_WARNING);
        }

        $logger->flush(true);

        // first rotate

        $logger->log(str_repeat('x', 1024), Logger::LEVEL_WARNING);
        $logger->flush(true);

        clearstatcache();

        self::assertLogFileState(
            $logFile,
            true,
        );

        // second rotate

        for ($i = 0; $i < 1024; $i++) {
            $logger->log(str_repeat('x', 1024), Logger::LEVEL_WARNING);
        }

        $logger->flush(true);

        clearstatcache();

        self::assertLogFileState(
            $logFile,
            true,
        );
    }

    public function testRotatePreservesMtime(): void
    {
        $logFile = Yii::getAlias('@yiiunit/runtime/log/filetargettest.log');

        FileHelper::removeDirectory(dirname($logFile));

        mkdir(dirname($logFile), 0777, true);

        $logger = new Logger();

        new Dispatcher(
            [
                'logger' => $logger,
                'targets' => [
                    'file' => [
                        'class' => 'yii\log\FileTarget',
                        'logFile' => $logFile,
                        'levels' => ['warning'],
                        'maxFileSize' => 1,
                        'maxLogFiles' => 1,
                        'logVars' => [],
                    ],
                ],
            ],
        );

        $logger->log(str_repeat('x', 2048), Logger::LEVEL_WARNING);
        $logger->flush(true);

        $expectedMtime = time() - 7200;

        touch($logFile, $expectedMtime);
        clearstatcache();

        $logger->log('y', Logger::LEVEL_WARNING);
        $logger->flush(true);

        clearstatcache();

        self::assertFileExists(
            $logFile . '.1',
            'Rotation must create the first archived log file.',
        );
        self::assertSame(
            $expectedMtime,
            filemtime($logFile . '.1'),
            'Rotation must preserve the original modification time.',
        );
    }

    #[DataProviderExternal(FileTargetProvider::class, 'messages')]
    public function testLogMessages(array $messages, ?array $expectedLines): void
    {
        $logFile = Yii::getAlias('@yiiunit/runtime/log/filetargettest.log');

        $this->clearLogFile($logFile);

        $logger = new CustomLogger();

        $logger->logFile = $logFile;
        $logger->messages = $messages;

        $logger->export();

        if ($expectedLines === null) {
            self::assertFileDoesNotExist(
                $logFile,
                'Ignored messages must not create a log file.',
            );

            return;
        }

        self::assertSame(
            $expectedLines,
            file($logFile),
            'Exported log lines must match the formatted messages exactly.',
        );
    }

    private static function assertLogFileState(string $logFile, bool $rotated): void
    {
        self::assertFileExists(
            $logFile,
            'The active log file must exist after flushing.',
        );

        if ($rotated) {
            self::assertFileExists(
                "{$logFile}.1",
                'The first archived log file must exist after rotation.',
            );
        } else {
            self::assertFileDoesNotExist(
                "{$logFile}.1",
                'The archive must not exist before rotation.',
            );
        }

        foreach (range(2, 4) as $suffix) {
            self::assertFileDoesNotExist(
                "{$logFile}.{$suffix}",
                "Log archive .{$suffix} must not exist when maxLogFiles is one.",
            );
        }
    }

    private function clearLogFile(string $logFile): void
    {
        FileHelper::removeDirectory(dirname((string) $logFile));

        mkdir(dirname((string) $logFile), 0777, true);
    }
}
