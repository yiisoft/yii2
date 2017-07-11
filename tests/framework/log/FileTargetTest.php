<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use Yii;
use yii\helpers\FileHelper;
use yii\log\Dispatcher;
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
            [true,  true],
            [false, true],
            [true,  false],
            [false, false],
        ];
    }

    /**
     * @dataProvider booleanDataProvider
     */
    public function testRotate($rotateByCopy, $compressFiles)
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
                    'rotateByCopy' => $rotateByCopy,
                    'compressRotatedFiles' => $compressFiles,
                ],
            ],
        ]);

        // one file

        $logger->log(str_repeat('x', 1024), Logger::LEVEL_WARNING);
        $logger->flush(true);

        clearstatcache();

        $this->assertFileExists($logFile);
        $this->assertFileNotExists($logFile . '.gz');
        $this->assertFileNotExists($logFile . '.1');
        $this->assertFileNotExists($logFile . '.1.gz');
        $this->assertFileNotExists($logFile . '.2');
        $this->assertFileNotExists($logFile . '.2.gz');
        $this->assertFileNotExists($logFile . '.3');
        $this->assertFileNotExists($logFile . '.3.gz');
        $this->assertFileNotExists($logFile . '.4');
        $this->assertFileNotExists($logFile . '.4.gz');

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
        $this->assertFileNotExists($logFile . '.gz');
        if ($compressFiles) {
            $this->assertFileNotExists($logFile . '.1');
            $this->assertFileExists($logFile . '.1.gz');
        } else {
            $this->assertFileExists($logFile . '.1');
            $this->assertFileNotExists($logFile . '.1.gz');
        }
        $this->assertFileNotExists($logFile . '.2');
        $this->assertFileNotExists($logFile . '.2.gz');
        $this->assertFileNotExists($logFile . '.3');
        $this->assertFileNotExists($logFile . '.3.gz');
        $this->assertFileNotExists($logFile . '.4');
        $this->assertFileNotExists($logFile . '.4.gz');

        // second rotate

        for ($i = 0; $i < 1024; $i++) {
            $logger->log(str_repeat('x', 1024), Logger::LEVEL_WARNING);
        }
        $logger->flush(true);

        clearstatcache();

        $this->assertFileExists($logFile);
        $this->assertFileNotExists($logFile . '.gz');
        if ($compressFiles) {
            $this->assertFileNotExists($logFile . '.1');
            $this->assertFileExists($logFile . '.1.gz');
        } else {
            $this->assertFileExists($logFile . '.1');
            $this->assertFileNotExists($logFile . '.1.gz');
        }
        $this->assertFileNotExists($logFile . '.2');
        $this->assertFileNotExists($logFile . '.2.gz');
        $this->assertFileNotExists($logFile . '.3');
        $this->assertFileNotExists($logFile . '.3.gz');
        $this->assertFileNotExists($logFile . '.4');
        $this->assertFileNotExists($logFile . '.4.gz');
    }
}
