<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\profile;

use yii\profile\LogTarget;
use yii\profile\Profiler;
use yii\profile\Target;
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
                '__class' => LogTarget::class,
                'logLevel' => 'test',
            ],
        ]);
        $target = $profiler->getTargets()[0];
        $this->assertTrue($target instanceof LogTarget);
        $this->assertEquals('test', $target->logLevel);
    }

    /**
     * @depends testSetupTarget
     *
     * @covers \yii\profile\Profiler::addTarget()
     */
    public function testAddTarget()
    {
        $profiler = new Profiler();

        $target = $this->getMockBuilder(Target::class)->getMockForAbstractClass();
        $profiler->setTargets([$target]);

        $namedTarget = $this->getMockBuilder(Target::class)->getMockForAbstractClass();
        $profiler->addTarget($namedTarget, 'test-target');

        $targets = $profiler->getTargets();
        $this->assertCount(2, $targets);
        $this->assertTrue(isset($targets['test-target']));
        $this->assertSame($namedTarget, $targets['test-target']);

        $namelessTarget = $this->getMockBuilder(Target::class)->getMockForAbstractClass();
        $profiler->addTarget($namelessTarget);
        $targets = $profiler->getTargets();
        $this->assertCount(3, $targets);
        $this->assertSame($namelessTarget, array_pop($targets));
    }

    public function testEnabled()
    {
        $profiler = new Profiler();

        $profiler->enabled = false;

        $profiler->begin('test');
        $profiler->end('test');

        $this->assertEmpty($profiler->messages);

        $profiler->enabled = true;

        $profiler->begin('test');
        $profiler->end('test');

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

        $profiler->begin('test');
        $profiler->begin('test');
        $profiler->end('test');
        $profiler->end('test');

        $this->assertCount(2, $profiler->messages);
    }

    /**
     * @depends testNestedMessages
     */
    public function testNestedLevel()
    {
        $profiler = new Profiler();

        $profiler->begin('outer');
        $profiler->begin('inner');
        $profiler->end('inner');
        $profiler->end('outer');
        $profiler->begin('not-nested');
        $profiler->end('not-nested');

        $outerMessage = null;
        $innerMessage = null;
        $notNestedMessage = null;
        foreach ($profiler->messages as $message) {
            if ($message['token'] === 'outer') {
                $outerMessage = $message;
                continue;
            }
            if ($message['token'] === 'inner') {
                $innerMessage = $message;
                continue;
            }
            if ($message['token'] === 'not-nested') {
                $notNestedMessage = $message;
                continue;
            }
        }

        $this->assertSame(0, $outerMessage['nestedLevel']);
        $this->assertSame(1, $innerMessage['nestedLevel']);
        $this->assertSame(0, $notNestedMessage['nestedLevel']);
    }
}