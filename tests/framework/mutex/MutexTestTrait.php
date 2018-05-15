<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\mutex;

use yii\mutex\Mutex;
use yii\mutex\SyncException;

/**
 * Class MutexTestTrait.
 */
trait MutexTestTrait
{
    public static $mutexName = 'testname';

    /**
     * @return Mutex
     * @throws \yii\base\InvalidConfigException
     */
    abstract protected function createMutex();

    public function testMutexAcquire()
    {
        $mutex = $this->createMutex();

        $this->assertTrue($mutex->acquire(self::$mutexName));
        $this->assertTrue($mutex->release(self::$mutexName));
    }

    public function testThatMutexLockIsWorking()
    {
        $mutexOne = $this->createMutex();
        $mutexTwo = $this->createMutex();

        $this->assertTrue($mutexOne->acquire(self::$mutexName));
        $this->assertFalse($mutexTwo->acquire(self::$mutexName));

        $mutexOne->release(self::$mutexName);

        $this->assertTrue($mutexTwo->acquire(self::$mutexName));

        $mutexTwo->release(self::$mutexName);
    }

    public function testSyncDone()
    {
        $mutex = $this->createMutex();

        $this->assertTrue($mutex->sync(self::$mutexName, 0, function () {
            return true;
        }));
    }

    public function testSyncFailedWithoutException()
    {
        $mutexOne = $this->createMutex();
        $mutexTwo = $this->createMutex();

        $this->assertTrue($mutexOne->acquire(self::$mutexName));
        $this->assertNull($mutexTwo->sync(self::$mutexName, 0, function () {
            return true;
        }, false));

        $mutexOne->release(self::$mutexName);
    }

    public function testSyncFailedWithException()
    {
        $mutexOne = $this->createMutex();
        $mutexTwo = $this->createMutex();

        $this->assertTrue($mutexOne->acquire(self::$mutexName));
        $this->expectException(SyncException::class);
        $mutexTwo->sync(self::$mutexName, 0, function () {
            return true;
        });
    }
}
