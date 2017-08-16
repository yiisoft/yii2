<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\profile;

use yii\profile\LogTarget;
use yii\profile\Profiler;
use yiiunit\TestCase;

/**
 * @group profile
 */
class ProfilerTest extends TestCase
{
    /**
     * @covers \yii\profile\Profiler::setTargets()
     * @covers \yii\profile\Profiler::getTargets()
     */
    public function testSetupTarget()
    {
        $profiler = new Profiler();

        $target = new LogTarget();
        $profiler->setTargets([$target]);

        $this->assertEquals([$target], $profiler->getTargets());
        $this->assertSame($target, $profiler->getTargets()[0]);

        $profiler->setTargets([
            [
                'class' => LogTarget::class,
                'logLevel' => 'test',
            ],
        ]);
        $target = $profiler->getTargets()[0];
        $this->assertTrue($target instanceof LogTarget);
        $this->assertEquals('test', $target->logLevel);
    }

    public function testEnabled()
    {
        $profiler = new Profiler();

        $profiler->enabled = false;

        $profiler->begin('test', 'test');
        $profiler->end('test', 'test');

        $this->assertEmpty($profiler->messages);

        $profiler->enabled = true;

        $profiler->begin('test', 'test');
        $profiler->end('test', 'test');

        $this->assertCount(1, $profiler->messages);
    }

    /**
     * @covers \yii\profile\Profiler::flush()
     */
    public function testFlushWithDispatch()
    {
        /* @var $profiler Profiler|\PHPUnit_Framework_MockObject_MockObject */
        $profiler = $this->getMockBuilder(Profiler::class)
            ->setMethods(['dispatch'])
            ->getMock();

        $message = ['anything'];
        $profiler->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo($message));

        $profiler->messages = $message;
        $profiler->flush();
        $this->assertEmpty($profiler->messages);
    }

    public function testNestedMessages()
    {
        $profiler = new Profiler();

        $profiler->begin('test', 'test');
        $profiler->begin('test', 'test');
        $profiler->end('test', 'test');
        $profiler->end('test', 'test');

        $this->assertCount(2, $profiler->messages);
    }
}