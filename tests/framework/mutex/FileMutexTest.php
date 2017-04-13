<?php

namespace yiiunit\framework\mutex;

use yii\mutex\FileMutex;
use yiiunit\TestCase;

/**
 * Class FileMutexTest
 *
 * @group mutex
 *
 * @package yii\tests\unit\framework\mutex
 */
class FileMutexTest extends TestCase
{
    use MutexTestTrait;

    /**
     * @return FileMutex
     * @throws \yii\base\InvalidConfigException
     */
    protected function createMutex()
    {
        return \Yii::createObject([
            'class' => FileMutex::class,
            'mutexPath' => '@yiiunit/runtime/mutex',
        ]);
    }

    public function testDeleteLockFile()
    {
        $mutex = $this->createMutex();
        $fileName = $mutex->mutexPath . '/' . md5(self::$mutexName) . '.lock';

        $mutex->acquire(self::$mutexName);
        $this->assertFileExists($fileName);

        $mutex->release(self::$mutexName);
        $this->assertFileNotExists($fileName);
    }
}
