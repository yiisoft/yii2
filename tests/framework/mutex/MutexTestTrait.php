<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\mutex;

use yii\mutex\Mutex;

/**
 * Class MutexTestTrait
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
    }

    public function testThatMutexLockIsWorking()
    {
        $mutexOne = $this->createMutex();
        $mutexTwo = $this->createMutex();

        $this->assertTrue($mutexOne->acquire(self::$mutexName));
        $this->assertFalse($mutexTwo->acquire(self::$mutexName));

        $mutexOne->release(self::$mutexName);

        $this->assertTrue($mutexTwo->acquire(self::$mutexName));
    }
}
