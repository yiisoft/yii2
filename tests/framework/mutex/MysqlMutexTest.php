<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\mutex;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Expression;
use yii\mutex\MysqlMutex;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * Class MysqlMutexTest.
 *
 * @group mutex
 * @group db
 * @group mysql
 */
class MysqlMutexTest extends DatabaseTestCase
{
    use MutexTestTrait;

    protected $driverName = 'mysql';

    /**
     * @param array $additionalParams additional params to component create
     * @return MysqlMutex
     * @throws InvalidConfigException
     */
    protected function createMutex($additionalParams = [])
    {
        return Yii::createObject(array_merge([
            'class' => MysqlMutex::class,
            'db' => $this->getConnection(),
        ], $additionalParams));
    }

    /**
     * @dataProvider mutexDataProvider()
     *
     * @param string $mutexName
     */
    public function testThatMutexLocksWithKeyPrefixesString($mutexName): void
    {
        $mutexOne = $this->createMutex(['keyPrefix' => 'a']);
        $mutexTwo = $this->createMutex(['keyPrefix' => 'b']);

        $this->assertTrue($mutexOne->acquire($mutexName));
        $this->assertTrue($mutexTwo->acquire($mutexName));
        $this->assertTrue($mutexOne->release($mutexName));
        $this->assertTrue($mutexTwo->release($mutexName));
    }

    /**
     * @dataProvider mutexDataProvider()
     *
     * @param string $mutexName
     */
    public function testThatMutexLocksWithKeyPrefixesLongString($mutexName): void
    {
        $mutexOne = $this->createMutex(['keyPrefix' => str_repeat('a', 40)]);
        $mutexTwo = $this->createMutex(['keyPrefix' => str_repeat('b', 40)]);

        $this->assertTrue($mutexOne->acquire($mutexName));
        $this->assertTrue($mutexTwo->acquire($mutexName));
        $this->assertTrue($mutexOne->release($mutexName));
        $this->assertTrue($mutexTwo->release($mutexName));
    }

    /**
     * @dataProvider mutexDataProvider()
     *
     * @param string $mutexName
     */
    public function testThatMutexLocksWithKeyPrefixesExpression($mutexName): void
    {
        $mutexOne = $this->createMutex(['keyPrefix' => new Expression('1+1')]);
        $mutexTwo = $this->createMutex(['keyPrefix' => new Expression('1+2')]);

        $this->assertTrue($mutexOne->acquire($mutexName));
        $this->assertTrue($mutexTwo->acquire($mutexName));
        $this->assertTrue($mutexOne->release($mutexName));
        $this->assertTrue($mutexTwo->release($mutexName));
    }

    /**
     * @dataProvider mutexDataProvider()
     *
     * @param string $mutexName
     */
    public function testThatMutexLocksWithKeyPrefixesExpressionCalculatedValue($mutexName): void
    {
        $mutexOne = $this->createMutex(['keyPrefix' => new Expression('1+1')]);
        $mutexTwo = $this->createMutex(['keyPrefix' => new Expression('1*2')]);

        $this->assertTrue($mutexOne->acquire($mutexName));
        $this->assertFalse($mutexTwo->acquire($mutexName));
        $this->assertTrue($mutexOne->release($mutexName));
    }

    public function testCreateMutex(): void
    {
        $mutex = $this->createMutex(['keyPrefix' => new Expression('1+1')]);
        $this->assertInstanceOf(MysqlMutex::class, $mutex);
        $this->assertInstanceOf(Expression::class, $mutex->keyPrefix);
        $this->assertSame('1+1', $mutex->keyPrefix->expression);
    }
}
