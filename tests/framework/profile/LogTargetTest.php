<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\profile;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Yii;
use yii\profile\LogTarget;
use yiiunit\TestCase;

class LogTargetTest extends TestCase
{
    /**
     * @covers \yii\profile\LogTarget::setLogger()
     * @covers \yii\profile\LogTarget::getLogger()
     */
    public function testSetupLogger()
    {
        $target = new LogTarget();

        $logger = new NullLogger();
        $target->setLogger($logger);
        $this->assertSame($logger, $target->getLogger());

        $target->setLogger(['__class' => NullLogger::class]);
        $this->assertNotSame($logger, $target->getLogger());
        $this->assertTrue($target->getLogger() instanceof NullLogger);

        $target->setLogger(null);
        $this->assertSame(Yii::getLogger(), $target->getLogger());
    }

    /**
     * @depends testSetupLogger
     *
     * @covers \yii\profile\LogTarget::export()
     */
    public function testExport()
    {
        /* @var $logger LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->setMethods([
                'log'
            ])
            ->getMockForAbstractClass();

        $target = new LogTarget();
        $target->setLogger($logger);
        $target->logLevel = 'test-level';

        $logger->expects($this->once())
            ->method('log')
            ->with($this->equalTo($target->logLevel), $this->equalTo('test-token'));

        $target->export([
            [
                'category' => 'test',
                'token' => 'test-token',
                'beginTime' => 123,
                'endTime' => 321,
            ],
        ]);
    }
}