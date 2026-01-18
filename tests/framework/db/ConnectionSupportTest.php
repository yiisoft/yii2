<?php

namespace yiiunit\framework\db;

use Yii;
use yii\db\Query;
use yii\db\Connection;
use yiiunit\TestCase;

// Дефинираме тест модел надвор од методот за подобра поддршка на Late Static Binding
class StubAuditLog extends \yii\db\ActiveRecord
{
    protected static $connection = 'db_logs';
    public static function tableName() { return 'audit_log'; }
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
        // Сега го повикуваме моделот кој експлицитно има дефинирано $connection
        $db = StubAuditLog::getDb();

        $this->assertInstanceOf(Connection::class, $db);
        // Ова ќе потврди дека ActiveRecord ја земал 'db_logs' конекцијата
        $this->assertEquals('mysql:host=localhost;dbname=logs_db_name', $db->dsn);
    }
}