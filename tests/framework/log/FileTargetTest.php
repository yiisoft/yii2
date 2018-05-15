<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use Psr\Log\LogLevel;
use Yii;
use yii\helpers\FileHelper;
use yii\log\FileTarget;
use yii\log\Logger;
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

    public function booleanDataProvider()
    {
        return [
            [true],
            [false],
        ];
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

    /**
     * @dataProvider booleanDataProvider
     * @param bool $rotateByCopy
     */
    public function testRotate($rotateByCopy)
    {
        $logFile = Yii::getAlias('@yiiunit/runtime/log/filetargettest.log');
        FileHelper::removeDirectory(dirname($logFile));
        mkdir(dirname($logFile), 0777, true);

        $logger = new Logger([
            'targets' => [
                'file' => [
                    '__class' => FileTarget::class,
                    'logFile' => $logFile,
                    'levels' => [LogLevel::WARNING],
                    'maxFileSize' => 1024, // 1 MB
                    'maxLogFiles' => 1, // one file for rotation and one normal log file
                    'logVars' => [],
                    'rotateByCopy' => $rotateByCopy,
                ],
            ],
        ]);

        // one file

        $logger->log(LogLevel::WARNING, str_repeat('x', 1024));
        $logger->flush(true);

        clearstatcache();

        $this->assertFileExists($logFile);
        $this->assertFileNotExists($logFile . '.1');
        $this->assertFileNotExists($logFile . '.2');
        $this->assertFileNotExists($logFile . '.3');
        $this->assertFileNotExists($logFile . '.4');

        // exceed max size
        for ($i = 0; $i < 1024; $i++) {
            $logger->log(LogLevel::WARNING, str_repeat('x', 1024));
        }
        $logger->flush(true);

        // first rotate

        $logger->log(LogLevel::WARNING, str_repeat('x', 1024));
        $logger->flush(true);

        clearstatcache();

        $this->assertFileExists($logFile);
        $this->assertFileExists($logFile . '.1');
        $this->assertFileNotExists($logFile . '.2');
        $this->assertFileNotExists($logFile . '.3');
        $this->assertFileNotExists($logFile . '.4');

        // second rotate

        for ($i = 0; $i < 1024; $i++) {
            $logger->log(LogLevel::WARNING, str_repeat('x', 1024));
        }
        $logger->flush(true);

        clearstatcache();

        $this->assertFileExists($logFile);
        $this->assertFileExists($logFile . '.1');
        $this->assertFileNotExists($logFile . '.2');
        $this->assertFileNotExists($logFile . '.3');
        $this->assertFileNotExists($logFile . '.4');
    }
}
