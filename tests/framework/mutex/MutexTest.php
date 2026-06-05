<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\mutex;

use yiiunit\framework\mutex\mocks\DumbMutex;
use yiiunit\TestCase;

/**
 * @group mutex
 */
class MutexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DumbMutex::$locked = false;
    }

    public function testAcquireAndRelease(): void
    {
        $mutex = new DumbMutex();
        $this->assertTrue($mutex->acquire('test'));
        $this->assertTrue($mutex->isAcquired('test'));
        $this->assertTrue($mutex->release('test'));
        $this->assertFalse($mutex->isAcquired('test'));
    }

    public function testAcquireSameLockTwiceReturnsFalse(): void
    {
        $mutex = new DumbMutex();
        $this->assertTrue($mutex->acquire('test'));
        $this->assertFalse($mutex->acquire('test'));
        $mutex->release('test');
    }

    public function testReleaseUnacquiredLockReturnsFalse(): void
    {
        $mutex = new DumbMutex();
        $this->assertFalse($mutex->release('test'));
    }

    public function testIsAcquiredReturnsFalseForUnknownLock(): void
    {
        $mutex = new DumbMutex();
        $this->assertFalse($mutex->isAcquired('nonexistent'));
    }

    public function testAutoReleaseIsEnabledByDefault(): void
    {
        $mutex = new DumbMutex();
        $this->assertTrue($mutex->autoRelease);
    }

    public function testAutoReleaseCanBeDisabled(): void
    {
        $mutex = new DumbMutex(['autoRelease' => false]);
        $this->assertFalse($mutex->autoRelease);
    }

    public function testAcquireSkipsAcquireLockWhenAlreadyHeld(): void
    {
        $mutex = new DumbMutex();
        $mutex->acquire('test');

        $countBefore = count($mutex->attemptsTime);
        $mutex->acquire('test');
        $this->assertCount($countBefore, $mutex->attemptsTime);

        $mutex->release('test');
    }
}
