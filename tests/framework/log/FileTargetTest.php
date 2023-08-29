<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use Yii;
use yii\helpers\FileHelper;
use yii\log\Dispatcher;
use yii\log\FileTarget;
use yii\log\Logger;
use yiiunit\framework\log\mocks\CustomLogger;
use yiiunit\TestCase;

/**
 * @group log
 */
class FileTargetTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    /**
     * Tests that log directory isn't created during init process
     * @see https://github.com/yiisoft/yii2/issues/15662
     */
    public function testInit()
    {
        $logFile = Yii::getAlias('@yiiunit/runtime/log/filetargettest.log');
        FileHelper::removeDirectory(dirname($logFile));
        new FileTarget([
            'logFile' => Yii::getAlias('@yiiunit/runtime/log/filetargettest.log'),
        ]);
        $this->assertFileNotExists(
            dirname($logFile),
            'Log directory should not be created during init process'
        );
    }

    public function testRotate()
    {
        $logFile = Yii::getAlias('@yiiunit/runtime/log/filetargettest.log');
        FileHelper::removeDirectory(dirname($logFile));
        mkdir(dirname($logFile), 0777, true);

        $logger = new Logger();
        $dispatcher = new Dispatcher([
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
        ]);

        // one file

        $logger->log(str_repeat('x', 1024), Logger::LEVEL_WARNING);
        $logger->flush(true);

        clearstatcache();

        $this->assertFileExists($logFile);
        $this->assertFileNotExists($logFile . '.1');
        $this->assertFileNotExists($logFile . '.2');
        $this->assertFileNotExists($logFile . '.3');
        $this->assertFileNotExists($logFile . '.4');

        // exceed max size
        for ($i = 0; $i < 1024; $i++) {
            $logger->log(str_repeat('x', 1024), Logger::LEVEL_WARNING);
        }
        $logger->flush(true);

        // first rotate

        $logger->log(str_repeat('x', 1024), Logger::LEVEL_WARNING);
        $logger->flush(true);

        clearstatcache();

        $this->assertFileExists($logFile);
        $this->assertFileExists($logFile . '.1');
        $this->assertFileNotExists($logFile . '.2');
        $this->assertFileNotExists($logFile . '.3');
        $this->assertFileNotExists($logFile . '.4');

        // second rotate

        for ($i = 0; $i < 1024; $i++) {
            $logger->log(str_repeat('x', 1024), Logger::LEVEL_WARNING);
        }
        $logger->flush(true);

        clearstatcache();

        $this->assertFileExists($logFile);
        $this->assertFileExists($logFile . '.1');
        $this->assertFileNotExists($logFile . '.2');
        $this->assertFileNotExists($logFile . '.3');
        $this->assertFileNotExists($logFile . '.4');
    }

    public function testLogEmptyStrings()
    {
        $logFile = Yii::getAlias('@yiiunit/runtime/log/filetargettest.log');
        $this->clearLogFile($logFile);

        $logger = new CustomLogger();
        $logger->logFile = $logFile;
        $logger->messages = array_fill(0, 1, 'xxx');
        $logger->export();

        $test = file($logFile);
        $this->assertEquals("xxx\n", $test[0]);

        $this->clearLogFile($logFile);

        $logger = new CustomLogger();
        $logger->logFile = $logFile;
        $logger->messages = array_fill(0, 3, 'xxx');
        $logger->export();

        $test = file($logFile);
        $this->assertEquals("xxx\n", $test[0]);
        $this->assertEquals("xxx\n", $test[1]);
        $this->assertEquals("xxx\n", $test[2]);

        $this->clearLogFile($logFile);

        $logger->messages = array_fill(0, 1, 'yyy');
        $logger->export();

        $this->assertFileNotExists($logFile);

        $logger->messages = array_fill(0, 10, '');
        $logger->export();

        $this->assertFileNotExists($logFile);

        $logger->messages = array_fill(0, 10, null);
        $logger->export();

        $this->assertFileNotExists($logFile);
    }

    private function clearLogFile($logFile)
    {
        FileHelper::removeDirectory(dirname($logFile));
        mkdir(dirname($logFile), 0777, true);
    }
}
