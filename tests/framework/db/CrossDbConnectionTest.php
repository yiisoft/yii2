<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

use yii\db\Connection;
use yii\db\ActiveQuery;
use yiiunit\TestCase;

/**
 * CrossDbConnectionTest tests cross-database join support.
 */
class CrossDbConnectionTest extends TestCase
{
    protected function createMockDb($driverName, $dsn)
    {
        $mock = $this->getMockBuilder(Connection::class)
            ->onlyMethods(['getDriverName', 'getSchema', 'quoteTableName'])
            ->getMock();

        $mock->method('getDriverName')->willReturn($driverName);
        $mock->dsn = $dsn;

        $mock->method('quoteTableName')->willReturnCallback(function ($name) use ($driverName) {
            if ($driverName === 'sqlsrv') {
                return "[$name]";
            } elseif ($driverName === 'pgsql') {
                return "\"$name\"";
            }
            return "`$name`";
        });

        return $mock;
    }

    /**
     * Tests if join correctly uses the database from useDb() call.
     * This test proves that useDb() overrides the default connection.
     */
    public function testJoinWithUseDb()
    {
        $dbMain = $this->createMockDb('mysql', 'mysql:host=localhost;dbname=main_db');
        $dbLogs = $this->createMockDb('mysql', 'mysql:host=localhost;dbname=logs_db');
        $dbOther = $this->createMockDb('mysql', 'mysql:host=localhost;dbname=other_db');

        $this->mockApplication(['components' => [
            'db' => $dbMain,
            'db_logs' => $dbLogs,
            'other_conn' => $dbOther
        ]]);

        $parent = new ActiveQuery(UserStub::class);
        $child = new ActiveQuery(LogStub::class);
        $child->link = ['user_id' => 'id'];
        $child->useDb('other_conn');

        $this->invokeMethod($parent, 'joinWithRelation', [$parent, $child, 'LEFT JOIN']);

        $joinTable = $parent->join[0][1];

        $this->assertStringContainsString('other_db', $joinTable, 'Join table should contain database name from useDb()');
        $this->assertStringContainsString('`other_db`', $joinTable, 'Database name should be quoted with backticks');
        $this->assertStringNotContainsString('logs_db', $joinTable, 'Should not use model connection (db_logs), but useDb() connection (other_conn)');
        $this->assertMatchesRegularExpression('/`other_db`\.`?audit_log`?/', $joinTable, 'Should match MySQL cross-database join format with useDb() connection');
    }

    public function testCrossDbJoinMySQL()
    {
        $db = $this->createMockDb('mysql', 'mysql:host=localhost;dbname=main_db');
        $dbLogs = $this->createMockDb('mysql', 'mysql:host=localhost;dbname=logs_db');

        $this->mockApplication(['components' => ['db' => $db, 'db_logs' => $dbLogs]]);

        $parent = new ActiveQuery(UserStub::class);
        $child = new ActiveQuery(LogStub::class);
        $child->link = ['user_id' => 'id'];

        $this->invokeMethod($parent, 'joinWithRelation', [$parent, $child, 'LEFT JOIN']);

        $joinTable = $parent->join[0][1];
        $this->assertMatchesRegularExpression('/`logs_db`\.`?audit_log`?/', $joinTable);
    }

    public function testCrossDbJoinMSSQL()
    {
        $db = $this->createMockDb('sqlsrv', 'sqlsrv:Server=localhost;Database=main_db');
        $dbLogs = $this->createMockDb('sqlsrv', 'sqlsrv:Server=localhost;Database=mssql_logs');

        $this->mockApplication(['components' => ['db' => $db, 'db_logs' => $dbLogs]]);

        $parent = new ActiveQuery(UserStub::class);
        $child = new ActiveQuery(LogStub::class);
        $child->link = ['user_id' => 'id'];

        $this->invokeMethod($parent, 'joinWithRelation', [$parent, $child, 'LEFT JOIN']);

        $joinTable = $parent->join[0][1];
        $this->assertMatchesRegularExpression('/\[mssql_logs\]\.\.\[?audit_log\]?/', $joinTable);
    }

    public function testCrossDbJoinSQLite()
    {
        $db = $this->createMockDb('sqlite', 'sqlite:/path/to/main.db');
        $dbLogs = $this->createMockDb('sqlite', 'sqlite:/path/to/logs.db');

        $this->mockApplication(['components' => ['db' => $db, 'db_logs' => $dbLogs]]);

        $parent = new ActiveQuery(UserStub::class);
        $child = new ActiveQuery(LogStub::class);
        $child->link = ['user_id' => 'id'];

        $this->invokeMethod($parent, 'joinWithRelation', [$parent, $child, 'LEFT JOIN']);

        $joinTable = $parent->join[0][1];
        $this->assertMatchesRegularExpression('/`logs`\.`?audit_log`?/', $joinTable);
    }

    public function testCrossDbJoinPostgreSQL()
    {
        $db = $this->createMockDb('pgsql', 'pgsql:host=localhost;dbname=main_db');
        $dbLogs = $this->createMockDb('pgsql', 'pgsql:host=localhost;dbname=logs_db');

        $this->mockApplication(['components' => ['db' => $db, 'db_logs' => $dbLogs]]);

        $parent = new ActiveQuery(UserStub::class);
        $child = new ActiveQuery(LogStub::class);
        $child->link = ['user_id' => 'id'];

        $this->invokeMethod($parent, 'joinWithRelation', [$parent, $child, 'LEFT JOIN']);

        $joinTable = $parent->join[0][1];
        $this->assertMatchesRegularExpression('/"logs_db"\.\"?audit_log\"?/', $joinTable);
    }
}
