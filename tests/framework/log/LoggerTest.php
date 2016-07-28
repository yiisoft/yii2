<?php
/**
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yiiunit\framework\log;

use yii\log\Logger;
use yiiunit\TestCase;

/**
 * @group log
 */
class LoggerTest extends TestCase
{
    /**
     * @var Logger
     */
    protected $logger;

    protected function setUp()
    {
        $this->logger = new Logger();
    }

    /**
     * @covers yii\log\Logger::Log()
     */
    public function testLog()
    {
        $this->logger->log('test1', Logger::LEVEL_INFO);
        $this->assertEquals(1, count($this->logger->messages));
        $this->assertEquals('test1', $this->logger->messages[0][0]);
        $this->assertEquals(Logger::LEVEL_INFO, $this->logger->messages[0][1]);
        $this->assertEquals('application', $this->logger->messages[0][2]);
        $this->assertEquals([], $this->logger->messages[0][4]);

        $this->logger->log('test2', Logger::LEVEL_ERROR, 'category');
        $this->assertEquals(2, count($this->logger->messages));
        $this->assertEquals('test2', $this->logger->messages[1][0]);
        $this->assertEquals(Logger::LEVEL_ERROR, $this->logger->messages[1][1]);
        $this->assertEquals('category', $this->logger->messages[1][2]);
        $this->assertEquals([], $this->logger->messages[1][4]);
    }

    /**
     * @covers yii\log\Logger::Log()
     */
    public function testLogWithTraceLevel()
    {
        $this->logger->traceLevel = 3;
        $this->logger->log('test3', Logger::LEVEL_INFO);
        $this->assertEquals(1, count($this->logger->messages));
        $this->assertEquals('test3', $this->logger->messages[0][0]);
        $this->assertEquals(Logger::LEVEL_INFO, $this->logger->messages[0][1]);
        $this->assertEquals('application', $this->logger->messages[0][2]);
        $this->assertEquals([
            'file' => __FILE__,
            'line' => 52,
            'function' => 'log',
            'class' => get_class($this->logger),
            'type' => '->'
        ], $this->logger->messages[0][4][0]);
        $this->assertEquals(3, count($this->logger->messages[0][4]));
    }

    /**
     * @covers yii\log\Logger::Log()
     */
    public function testLogWithFlush()
    {
        $logger = $this->getMock('yii\\log\\Logger', ['flush']);
        $logger->flushInterval = 1;
        $logger->expects($this->exactly(1))->method('flush');
        $logger->log('test1', Logger::LEVEL_INFO);
    }
}
