<?php

namespace yiiunit\framework\db;

use Yii;
use yii\db\Connection;
use yii\db\ActiveQuery;
use yiiunit\TestCase;
use ReflectionMethod;

class UserStub extends \yii\db\ActiveRecord
{
    protected static $connection = 'db';

    public static function tableName()
    {
        return 'user';
    }
}

class LogStub extends \yii\db\ActiveRecord
{
    protected static $connection = 'db_logs';

    public static function tableName()
    {
        return 'audit_log';
    }
}

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

        $this->assertStringContainsString('logs_db', $joinTable, 'Join table should contain database name');
        $this->assertStringContainsString('`logs_db`', $joinTable, 'Database name should be quoted with backticks');
        $this->assertStringContainsString('.', $joinTable, 'Should have dot separator');
        $this->assertStringNotContainsString('..', $joinTable, 'MySQL should use single dot, not double dots');
        $this->assertMatchesRegularExpression('/`logs_db`\.`?audit_log`?/', $joinTable, 'Should match MySQL cross-database join format: `db_name`.`table_name`');
        $this->assertTrue(
            preg_match('/`logs_db`\./', $joinTable) === 1,
            'MySQL join should have quoted database name followed by dot: `logs_db`.'
        );
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

        $this->assertStringContainsString('mssql_logs', $joinTable, 'Join table should contain database name');
        $this->assertStringContainsString('[mssql_logs]', $joinTable, 'Database name should be quoted with brackets');
        $this->assertStringContainsString('..', $joinTable, 'MSSQL must use double dots separator (not single dot)');
        $this->assertMatchesRegularExpression('/\[mssql_logs\]\.\.\[?audit_log\]?/', $joinTable, 'Should match MSSQL cross-database join format: [db_name]..[table_name]');
        $this->assertTrue(
            preg_match('/\[mssql_logs\]\.\./', $joinTable) === 1,
            'MSSQL join should have quoted database name followed by double dots: [mssql_logs]..'
        );
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

        $this->assertStringContainsString('logs', $joinTable, 'Join table should contain database name (from filename)');
        $this->assertStringContainsString('`logs`', $joinTable, 'Database name should be quoted with backticks');
        $this->assertStringContainsString('.', $joinTable, 'Should have dot separator');
        $this->assertStringNotContainsString('..', $joinTable, 'SQLite should use single dot, not double dots');
        $this->assertMatchesRegularExpression('/`logs`\.`?audit_log`?/', $joinTable, 'Should match SQLite cross-database join format: `db_name`.`table_name`');
        $this->assertNotEquals($db->dsn, $dbLogs->dsn, 'Parent and child should have different DSNs for cross-database join');
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

        $this->assertStringContainsString('logs_db', $joinTable, 'Join table should contain database name');
        $this->assertStringContainsString('"logs_db"', $joinTable, 'Database name should be quoted with double quotes');
        $this->assertStringContainsString('.', $joinTable, 'Should have dot separator');
        $this->assertStringNotContainsString('..', $joinTable, 'PostgreSQL should use single dot, not double dots');
        $this->assertMatchesRegularExpression('/"logs_db"\.\"?audit_log\"?/', $joinTable, 'Should match PostgreSQL cross-database join format: "db_name"."table_name"');
        $this->assertTrue(
            preg_match('/"logs_db"\./', $joinTable) === 1,
            'PostgreSQL join should have quoted database name followed by dot: "logs_db".'
        );
    }
}