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
use yii\mutex\DbMutex;
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
        $mutex = $this->getMockForAbstractClass(DbMutex::class);
        $this->assertInstanceOf(Connection::class, $mutex->db);
    }

    public function testDbPropertyResolvesFromApplicationComponent(): void
    {
        $mutex = $this->getMockForAbstractClass(DbMutex::class);
        $this->assertSame(Yii::$app->db, $mutex->db);
    }

    public function testDbPropertyAcceptsConnectionObject(): void
    {
        $connection = new Connection(['dsn' => 'sqlite::memory:']);
        $mutex = $this->getMockForAbstractClass(DbMutex::class, [['db' => $connection]]);
        $this->assertSame($connection, $mutex->db);
    }

    public function testDbPropertyAcceptsConfigArray(): void
    {
        $mutex = $this->getMockForAbstractClass(DbMutex::class, [[
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
        $this->getMockForAbstractClass(DbMutex::class, [['db' => 'nonExistingComponent']]);
    }
}
