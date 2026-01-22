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
use ReflectionMethod;

/**
 * ConnectionSupportTest tests cross-database join support.
 */
class ConnectionSupportTest extends TestCase
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

    public function testCrossDbJoinMySQL()
    {
        $db = $this->createMockDb('mysql', 'mysql:host=localhost;dbname=main_db');
        $dbLogs = $this->createMockDb('mysql', 'mysql:host=localhost;dbname=logs_db');

        $this->mockApplication(['components' => ['db' => $db, 'db_logs' => $dbLogs]]);

        $parent = new ActiveQuery(UserStub::class);
        $child = new ActiveQuery(LogStub::class);
        $child->link = ['user_id' => 'id'];

        $method = new ReflectionMethod($parent, 'joinWithRelation');
        $method->setAccessible(true);
        $method->invoke($parent, $parent, $child, 'LEFT JOIN');

        $this->assertNotEmpty($parent->join, 'Join should be added to parent query');
        $joinTable = $parent->join[0][1];

        $this->assertStringContainsString('logs_db', $joinTable);
        $this->assertStringContainsString('`logs_db`', $joinTable);
        $this->assertStringContainsString('.', $joinTable);
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

        $method = new ReflectionMethod($parent, 'joinWithRelation');
        $method->setAccessible(true);
        $method->invoke($parent, $parent, $child, 'LEFT JOIN');

        $this->assertNotEmpty($parent->join, 'Join should be added to parent query');
        $joinTable = $parent->join[0][1];

        $this->assertStringContainsString('[mssql_logs]', $joinTable);
        $this->assertStringContainsString('..', $joinTable);
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

        $method = new ReflectionMethod($parent, 'joinWithRelation');
        $method->setAccessible(true);
        $method->invoke($parent, $parent, $child, 'LEFT JOIN');

        $this->assertNotEmpty($parent->join, 'Join should be added to parent query');
        $joinTable = $parent->join[0][1];

        $this->assertStringContainsString('`logs`', $joinTable);
        $this->assertStringContainsString('.', $joinTable);
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

        $method = new ReflectionMethod($parent, 'joinWithRelation');
        $method->setAccessible(true);
        $method->invoke($parent, $parent, $child, 'LEFT JOIN');

        $this->assertNotEmpty($parent->join, 'Join should be added to parent query');
        $joinTable = $parent->join[0][1];

        $this->assertStringContainsString('"logs_db"', $joinTable);
        $this->assertStringContainsString('.', $joinTable);
        $this->assertMatchesRegularExpression('/"logs_db"\.\"?audit_log\"?/', $joinTable);
    }
}
