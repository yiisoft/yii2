<?php

namespace yiiunit\framework\db\sqlite;

use yii\db\Schema;
use yiiunit\framework\db\QueryBuilderTest;

/**
 * @group db
 * @group sqlite
 */
class SqliteQueryBuilderTest extends QueryBuilderTest
{
    protected $driverName = 'sqlite';

    public function columnTypes()
    {
        return [
            [Schema::TYPE_PK, Schema::primaryKey(), 'integer PRIMARY KEY AUTOINCREMENT NOT NULL'],
            [Schema::TYPE_PK . '(8)', Schema::primaryKey(8), 'integer PRIMARY KEY AUTOINCREMENT NOT NULL'],
            [Schema::TYPE_PK . ' CHECK (value > 5)', Schema::primaryKey()->check('value > 5'), 'integer PRIMARY KEY AUTOINCREMENT NOT NULL CHECK (value > 5)'],
            [Schema::TYPE_PK . '(8) CHECK (value > 5)', Schema::primaryKey(8)->check('value > 5'), 'integer PRIMARY KEY AUTOINCREMENT NOT NULL CHECK (value > 5)'],
            [Schema::TYPE_STRING, Schema::string(), 'varchar(255)'],
            [Schema::TYPE_STRING . '(32)', Schema::string(32), 'varchar(32)'],
            [Schema::TYPE_STRING . ' CHECK (value LIKE "test%")', Schema::string()->check('value LIKE "test%"'), 'varchar(255) CHECK (value LIKE "test%")'],
            [Schema::TYPE_STRING . '(32) CHECK (value LIKE "test%")', Schema::string(32)->check('value LIKE "test%"'), 'varchar(32) CHECK (value LIKE "test%")'],
            [Schema::TYPE_STRING . ' NOT NULL', Schema::string()->notNull(), 'varchar(255) NOT NULL'],
            [Schema::TYPE_TEXT, Schema::text(), 'text'],
            [Schema::TYPE_TEXT . '(255)', Schema::text(255), 'text'],
            [Schema::TYPE_TEXT . ' CHECK (value LIKE "test%")', Schema::text()->check('value LIKE "test%"'), 'text CHECK (value LIKE "test%")'],
            [Schema::TYPE_TEXT . '(255) CHECK (value LIKE "test%")', Schema::text(255)->check('value LIKE "test%"'), 'text CHECK (value LIKE "test%")'],
            [Schema::TYPE_TEXT . ' NOT NULL', Schema::text()->notNull(), 'text NOT NULL'],
            [Schema::TYPE_TEXT . '(255) NOT NULL', Schema::text(255)->notNull(), 'text NOT NULL'],
            [Schema::TYPE_SMALLINT, Schema::smallInteger(), 'smallint'],
            [Schema::TYPE_SMALLINT . '(8)', Schema::smallInteger(8), 'smallint'],
            [Schema::TYPE_INTEGER, Schema::integer(), 'integer'],
            [Schema::TYPE_INTEGER . '(8)', Schema::integer(8), 'integer'],
            [Schema::TYPE_INTEGER . ' CHECK (value > 5)', Schema::integer()->check('value > 5'), 'integer CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . '(8) CHECK (value > 5)', Schema::integer(8)->check('value > 5'), 'integer CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . ' NOT NULL', Schema::integer()->notNull(), 'integer NOT NULL'],
            [Schema::TYPE_BIGINT, Schema::bigInteger(), 'bigint'],
            [Schema::TYPE_BIGINT . '(8)', Schema::bigInteger(8), 'bigint'],
            [Schema::TYPE_BIGINT . ' CHECK (value > 5)', Schema::bigInteger()->check('value > 5'), 'bigint CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . '(8) CHECK (value > 5)', Schema::bigInteger(8)->check('value > 5'), 'bigint CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . ' NOT NULL', Schema::bigInteger()->notNull(), 'bigint NOT NULL'],
            [Schema::TYPE_FLOAT, Schema::float(), 'float'],
            [Schema::TYPE_FLOAT . '(16,5)', Schema::float(16, 5), 'float'],
            [Schema::TYPE_FLOAT . ' CHECK (value > 5.6)', Schema::float()->check('value > 5.6'), 'float CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . '(16,5) CHECK (value > 5.6)', Schema::float(16, 5)->check('value > 5.6'), 'float CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . ' NOT NULL', Schema::float()->notNull(), 'float NOT NULL'],
            [Schema::TYPE_DECIMAL, Schema::decimal(), 'decimal(10,0)'],
            [Schema::TYPE_DECIMAL . '(12,4)', Schema::decimal(12, 4), 'decimal(12,4)'],
            [Schema::TYPE_DECIMAL . ' CHECK (value > 5.6)', Schema::decimal()->check('value > 5.6'), 'decimal(10,0) CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . '(12,4) CHECK (value > 5.6)', Schema::decimal(12, 4)->check('value > 5.6'), 'decimal(12,4) CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . ' NOT NULL', Schema::decimal()->notNull(), 'decimal(10,0) NOT NULL'],
            [Schema::TYPE_DATETIME, Schema::dateTime(), 'datetime'],
            [Schema::TYPE_DATETIME . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", Schema::dateTime()->check("value BETWEEN '2011-01-01' AND '2013-01-01'"), "datetime CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATETIME . ' NOT NULL', Schema::dateTime()->notNull(), 'datetime NOT NULL'],
            [Schema::TYPE_TIMESTAMP, Schema::timestamp(), 'timestamp'],
            [Schema::TYPE_TIMESTAMP . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", Schema::timestamp()->check("value BETWEEN '2011-01-01' AND '2013-01-01'"), "timestamp CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_TIMESTAMP . ' NOT NULL', Schema::timestamp()->notNull(), 'timestamp NOT NULL'],
            [Schema::TYPE_TIME, Schema::time(), 'time'],
            [Schema::TYPE_TIME . " CHECK (value BETWEEN '12:00:00' AND '13:01:01')", Schema::time()->check("value BETWEEN '12:00:00' AND '13:01:01'"), "time CHECK (value BETWEEN '12:00:00' AND '13:01:01')"],
            [Schema::TYPE_TIME . ' NOT NULL', Schema::time()->notNull(), 'time NOT NULL'],
            [Schema::TYPE_DATE, Schema::date(), 'date'],
            [Schema::TYPE_DATE . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", Schema::date()->check("value BETWEEN '2011-01-01' AND '2013-01-01'"), "date CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATE . ' NOT NULL', Schema::date()->notNull(), 'date NOT NULL'],
            [Schema::TYPE_BINARY, Schema::binary(), 'blob'],
            [Schema::TYPE_BOOLEAN, Schema::boolean(), 'boolean'],
            [Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1', Schema::boolean()->notNull()->default(1), 'boolean NOT NULL DEFAULT 1'],
            [Schema::TYPE_MONEY, Schema::money(), 'decimal(19,4)'],
            [Schema::TYPE_MONEY . '(16,2)', Schema::money(16, 2), 'decimal(16,2)'],
            [Schema::TYPE_MONEY . ' CHECK (value > 0.0)', Schema::money()->check('value > 0.0'), 'decimal(19,4) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . '(16,2) CHECK (value > 0.0)', Schema::money(16, 2)->check('value > 0.0'),  'decimal(16,2) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . ' NOT NULL', Schema::money()->notNull(), 'decimal(19,4) NOT NULL'],
        ];
    }

    public function testAddDropPrimaryKey()
    {
        $this->setExpectedException('yii\base\NotSupportedException');
        parent::testAddDropPrimaryKey();
    }

    public function testBatchInsert()
    {
        $db = $this->getConnection();
        if (version_compare($db->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION), '3.7.11', '>=')) {
            $this->markTestSkipped('This test is only relevant for SQLite < 3.7.11');
        }
        $sql = $this->getQueryBuilder()->batchInsert('{{customer}} t', ['t.id', 't.name'], [[1, 'a'], [2, 'b']]);
        $this->assertEquals("INSERT INTO {{customer}} t (`t`.`id`, `t`.`name`) SELECT 1, 'a' UNION SELECT 2, 'b'", $sql);
    }
}
