<?php
/**
 * @author qihuajun <qihjun@gmail.com>
 */

namespace yiiunit\framework\log;

use yii\helpers\FileHelper;
use yii\log\Dispatcher;
use yii\log\Logger;
use Yii;
use yiiunit\TestCase;

/**
 * @group log
 */
class LogStashTargetTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }


    /**
     *  test generated logs are json format
     *
     */
    public function testGenerateLog()
    {
        $logFile = Yii::getAlias('@yiiunit/runtime/log/logstashtargettest.log');
        FileHelper::removeDirectory(dirname($logFile));
        mkdir(dirname($logFile), 0777, true);

        $logger = new Logger();
        $dispatcher = new Dispatcher([
            'logger' => $logger,
            'targets' => [
                'file' => [
                    'class' => 'yii\log\LogStashTarget',
                    'logFile' => $logFile,
                ]
            ]
        ]);

        // one file

        $logger->log(str_repeat('x', 1024), Logger::LEVEL_WARNING);
        $logger->flush(true);

        clearstatcache();

        $this->assertTrue(file_exists($logFile));

        $logs = file($logFile);
        $this->assertNotEmpty($logs);

        $log = json_decode($logs[0],true);
        $this->assertNotEmpty($log);
        $this->assertArrayHasKey('message',$log);
    }
}