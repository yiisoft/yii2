<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use PHPUnit\Framework\Attributes\Group;
use PDO;
use yii\db\Connection;
use yiiunit\base\db\BaseConnection;

/**
 * Unit tests for {@see \yii\db\mysql\Connection} functionality for the MySQL driver.
 */
#[Group('db')]
#[Group('mysql')]
#[Group('connection')]
class ConnectionTest extends BaseConnection
{
    protected $driverName = 'mysql';

    public function testGetEffectiveCharsetResolutionOrder(): void
    {
        $db = new Connection(['dsn' => 'mysql:host=127.0.0.1;dbname=yiitest', 'charset' => 'latin1']);

        self::assertSame(
            'latin1',
            $db->effectiveCharset,
            'Explicit charset must win.',
        );

        $db = new Connection(['dsn' => 'mysql:host=127.0.0.1;dbname=yiitest;charset=utf8mb3']);

        self::assertSame(
            'utf8mb3',
            $db->effectiveCharset,
            "DSN charset must be used when the property is 'null'.",
        );

        $db = new Connection(['dsn' => 'mysql:host=127.0.0.1;dbname=yiitest']);

        self::assertSame(
            'utf8mb4',
            $db->effectiveCharset,
            "Fallback must be 'utf8mb4'.",
        );
    }

    public function testInitConnectionDefaultsToUtf8mb4WhenNoCharsetIsConfigured(): void
    {
        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::once())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->expects(self::once())
            ->method('quote')
            ->with('utf8mb4')
            ->willReturn("'utf8mb4'");
        $pdo->expects(self::once())
            ->method('exec')
            ->with("SET NAMES 'utf8mb4'");

        $db = new Connection(['dsn' => 'mysql:host=127.0.0.1;dbname=yiitest']);

        $db->pdo = $pdo;

        $this->invokeMethod($db, 'initConnection');
    }

    public function testInitConnectionDoesNotOverrideDsnCharset(): void
    {
        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::once())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->expects(self::never())
            ->method('quote');
        $pdo->expects(self::never())
            ->method('exec');

        $db = new Connection(['dsn' => 'mysql:host=127.0.0.1;dbname=yiitest;charset=utf8mb3']);

        $db->pdo = $pdo;

        $this->invokeMethod($db, 'initConnection');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testTransactionAutocommit(): void
    {
        /** @var Connection $connection */
        $connection = $this->getConnection(true);
        $connection->transaction(function (Connection $db): void {
            // create table will cause the transaction to be implicitly committed
            // (see https://dev.mysql.com/doc/refman/8.0/en/implicit-commit.html)
            $name = 'test_implicit_transaction_table';
            $db->createCommand()->createTable($name, ['id' => 'pk'])->execute();
            $db->createCommand()->dropTable($name)->execute();
        });
        // If we made it this far without an error, then everything's working
    }
}
