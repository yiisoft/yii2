<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\mutex;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yiiunit\TestCase;

/**
 * @group mutex
 * @group db
 */
class DbMutexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication([
            'components' => [
                'db' => [
                    'class' => Connection::class,
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ]);
    }

    public function testDbPropertyDefaultValue(): void
    {
        $mutex = $this->getMockForAbstractClass('yii\mutex\DbMutex');
        $this->assertInstanceOf(Connection::class, $mutex->db);
    }

    public function testDbPropertyResolvesFromApplicationComponent(): void
    {
        $mutex = $this->getMockForAbstractClass('yii\mutex\DbMutex');
        $this->assertSame(Yii::$app->db, $mutex->db);
    }

    public function testDbPropertyAcceptsConnectionObject(): void
    {
        $connection = new Connection(['dsn' => 'sqlite::memory:']);
        $mutex = $this->getMockForAbstractClass('yii\mutex\DbMutex', [['db' => $connection]]);
        $this->assertSame($connection, $mutex->db);
    }

    public function testDbPropertyAcceptsConfigArray(): void
    {
        $mutex = $this->getMockForAbstractClass('yii\mutex\DbMutex', [[
            'db' => [
                'class' => Connection::class,
                'dsn' => 'sqlite::memory:',
            ],
        ]]);
        $this->assertInstanceOf(Connection::class, $mutex->db);
    }

    public function testThrowsExceptionForInvalidDb(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->getMockForAbstractClass('yii\mutex\DbMutex', [['db' => 'nonExistingComponent']]);
    }
}
