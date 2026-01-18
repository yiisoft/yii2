<?php

namespace yiiunit\framework\db;

use yii\db\Query;
use yii\db\Connection;
use yiiunit\TestCase;

class StubAuditLog extends \yii\db\ActiveRecord
{
    protected static $connection = 'db_logs';
    public static function tableName()
    {
        return 'audit_log';
    }
}

class ConnectionSupportTest extends TestCase
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
                'db_logs' => [
                    'class' => Connection::class,
                    'dsn' => 'mysql:host=localhost;dbname=logs_db_name',
                ],
            ],
        ]);
    }

    public function testUseDb()
    {
        $query = new Query();
        $query->useDb('db_logs');
        $this->assertEquals('db_logs', $query->db);
    }

    public function testActiveRecordConnectionProperty()
    {
        $db = StubAuditLog::getDb();

        $this->assertInstanceOf(Connection::class, $db);
        $this->assertEquals('mysql:host=localhost;dbname=logs_db_name', $db->dsn);
    }
}
