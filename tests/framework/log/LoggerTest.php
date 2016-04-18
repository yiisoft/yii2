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

    public function testLog()
    {
        $logger = new Logger();

        $logger->log('test1', Logger::LEVEL_INFO);
        $this->assertEquals(1, count($logger->messages));
        $this->assertEquals('test1', $logger->messages[0][0]);
        $this->assertEquals(Logger::LEVEL_INFO, $logger->messages[0][1]);
        $this->assertEquals('application', $logger->messages[0][2]);

        $logger->log('test2', Logger::LEVEL_ERROR, 'category');
        $this->assertEquals(2, count($logger->messages));
        $this->assertEquals('test2', $logger->messages[1][0]);
        $this->assertEquals(Logger::LEVEL_ERROR, $logger->messages[1][1]);
        $this->assertEquals('category', $logger->messages[1][2]);
    }
}
