<?php

namespace yii\tests\unit\framework\mutex;

use yii\mutex\PgsqlMutex;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * Class PgsqlMutexTest
 *
 * @package yii\tests\unit\framework\mutex
 */
class PgsqlMutexTest extends DatabaseTestCase
{
    const MUTEX_NAME = 'testname';

    protected $driverName = 'pgsql';

    public function testMutexAcquire()
    {
        $mutex = $this->createMutex();

        $this->assertTrue($mutex->acquire(self::MUTEX_NAME));
    }

    /**
     * @return PgsqlMutex
     * @throws \yii\base\InvalidConfigException
     */
    private function createMutex()
    {
        return \Yii::createObject([
            'class' => PgsqlMutex::className(),
            'db' => $this->getConnection(),
        ]);
    }

    public function testThatMutexLockIsWorking()
    {
        $mutexOne = $this->createMutex();
        $mutexTwo = $this->createMutex();

        $this->assertTrue($mutexOne->acquire(self::MUTEX_NAME));
        $this->assertFalse($mutexTwo->acquire(self::MUTEX_NAME));

        $mutexOne->release(self::MUTEX_NAME);

        $this->assertTrue($mutexTwo->acquire(self::MUTEX_NAME));
    }
}
