<?php

namespace yiiunit\framework\db\mssql;

use yii\db\mssql\Schema;
use yiiunit\framework\db\QueryBuilderTest;
use yii\db\Query;

/**
 * @group db
 * @group mssql
 */
class MssqlQueryBuilderTest extends QueryBuilderTest
{
    public $driverName = 'sqlsrv';

    /**
     * this is not used as a dataprovider for testGetColumnType to speed up the test
     * when used as dataprovider every single line will cause a reconnect with the database which is not needed here
     */
    public function columnTypes()
    {
        return [
            [Schema::TYPE_PK, $this->primaryKey(), 'int IDENTITY PRIMARY KEY'],
            [Schema::TYPE_PK . '(8)', $this->primaryKey(8), 'int IDENTITY PRIMARY KEY'],
            [Schema::TYPE_PK . ' CHECK (value > 5)', $this->primaryKey()->check('value > 5'), 'int IDENTITY PRIMARY KEY CHECK (value > 5)'],
            [Schema::TYPE_PK . '(8) CHECK (value > 5)', $this->primaryKey(8)->check('value > 5'), 'int IDENTITY PRIMARY KEY CHECK (value > 5)'],
            [Schema::TYPE_STRING, $this->string(), 'varchar(255)'],
            [Schema::TYPE_STRING . '(32)', $this->string(32), 'varchar(32)'],
            [Schema::TYPE_STRING . ' CHECK (value LIKE \'test%\')', $this->string()->check('value LIKE \'test%\''), 'varchar(255) CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_STRING . '(32) CHECK (value LIKE \'test%\')', $this->string(32)->check('value LIKE \'test%\''), 'varchar(32) CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_STRING . ' NOT NULL', $this->string()->notNull(), 'varchar(255) NOT NULL'],
            [Schema::TYPE_TEXT, $this->text(), 'text'],
            [Schema::TYPE_TEXT . '(255)', $this->text(), 'text', Schema::TYPE_TEXT],
            [Schema::TYPE_TEXT . ' NOT NULL', $this->text()->notNull(), 'text NOT NULL'],
            [Schema::TYPE_TEXT . '(255) NOT NULL', $this->text()->notNull(), 'text NOT NULL', Schema::TYPE_TEXT . ' NOT NULL'],
            [Schema::TYPE_SMALLINT, $this->smallInteger(), 'smallint'],
            [Schema::TYPE_SMALLINT . '(8)', $this->smallInteger(8), 'smallint'],
            [Schema::TYPE_INTEGER, $this->integer(), 'int'],
            [Schema::TYPE_INTEGER . '(8)', $this->integer(8), 'int'],
            [Schema::TYPE_INTEGER . ' CHECK (value > 5)', $this->integer()->check('value > 5'), 'int CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . '(8) CHECK (value > 5)', $this->integer(8)->check('value > 5'), 'int CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . ' NOT NULL', $this->integer()->notNull(), 'int NOT NULL'],
            [Schema::TYPE_BIGINT, $this->bigInteger(), 'bigint'],
            [Schema::TYPE_BIGINT . '(8)', $this->bigInteger(8), 'bigint'],
            [Schema::TYPE_BIGINT . ' CHECK (value > 5)', $this->bigInteger()->check('value > 5'), 'bigint CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . '(8) CHECK (value > 5)', $this->bigInteger(8)->check('value > 5'), 'bigint CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . ' NOT NULL', $this->bigInteger()->notNull(), 'bigint NOT NULL'],
            [Schema::TYPE_FLOAT, $this->float(), 'float'],
            [Schema::TYPE_FLOAT . '(16)', $this->float(16), 'float'],
            [Schema::TYPE_FLOAT . ' CHECK (value > 5.6)', $this->float()->check('value > 5.6'), 'float CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . '(16) CHECK (value > 5.6)', $this->float(16)->check('value > 5.6'), 'float CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . ' NOT NULL', $this->float()->notNull(), 'float NOT NULL'],
            [Schema::TYPE_DECIMAL, $this->decimal(), 'decimal'],
            [Schema::TYPE_DECIMAL . '(12,4)', $this->decimal(12, 4), 'decimal'],
            [Schema::TYPE_DECIMAL . ' CHECK (value > 5.6)', $this->decimal()->check('value > 5.6'), 'decimal CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . '(12,4) CHECK (value > 5.6)', $this->decimal(12, 4)->check('value > 5.6'), 'decimal CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . ' NOT NULL', $this->decimal()->notNull(), 'decimal NOT NULL'],
            [Schema::TYPE_DATETIME, $this->dateTime(), 'datetime'],
            [Schema::TYPE_DATETIME . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", $this->dateTime()->check("value BETWEEN '2011-01-01' AND '2013-01-01'"), "datetime CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATETIME . ' NOT NULL', $this->dateTime()->notNull(), 'datetime NOT NULL'],
            [Schema::TYPE_TIMESTAMP, $this->timestamp(), 'timestamp'],
            [Schema::TYPE_TIME, $this->time(), 'time'],
            [Schema::TYPE_TIME . " CHECK (value BETWEEN '12:00:00' AND '13:01:01')", $this->time()->check("value BETWEEN '12:00:00' AND '13:01:01'"), "time CHECK (value BETWEEN '12:00:00' AND '13:01:01')"],
            [Schema::TYPE_TIME . ' NOT NULL', $this->time()->notNull(), 'time NOT NULL'],
            [Schema::TYPE_DATE, $this->date(), 'date'],
            [Schema::TYPE_DATE . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", $this->date()->check("value BETWEEN '2011-01-01' AND '2013-01-01'"), "date CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATE . ' NOT NULL', $this->date()->notNull(), 'date NOT NULL'],
            [Schema::TYPE_BINARY, $this->binary(1), 'binary(1)', Schema::TYPE_BINARY .'(1)'],
            [Schema::TYPE_BOOLEAN, $this->boolean(), 'bit'],
            [Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1', $this->boolean()->notNull()->defaultValue(1), 'bit NOT NULL DEFAULT 1'],
            [Schema::TYPE_MONEY, $this->money(), 'decimal(19,4)'],
            [Schema::TYPE_MONEY . '(16,2)', $this->money(16, 2), 'decimal(16,2)'],
            [Schema::TYPE_MONEY . ' CHECK (value > 0.0)', $this->money()->check('value > 0.0'), 'decimal(19,4) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . '(16,2) CHECK (value > 0.0)', $this->money(16, 2)->check('value > 0.0'), 'decimal(16,2) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . ' NOT NULL', $this->money()->notNull(), 'decimal(19,4) NOT NULL'],
        ];
    }

    public function testOffsetLimit()
    {
        $expectedQuerySql = 'SELECT [id] FROM [example] ORDER BY (SELECT NULL) OFFSET 5 ROWS FETCH NEXT 10 ROWS ONLY';
        $expectedQueryParams = [];

        $query = new Query();
        $query->select('id')->from('example')->limit(10)->offset(5);

        list($actualQuerySql, $actualQueryParams) = $this->getQueryBuilder()->build($query);

        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals($expectedQueryParams, $actualQueryParams);
    }

    public function testLimit()
    {
        $expectedQuerySql = 'SELECT [id] FROM [example] ORDER BY (SELECT NULL) OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY';
        $expectedQueryParams = [];

        $query = new Query();
        $query->select('id')->from('example')->limit(10);

        list($actualQuerySql, $actualQueryParams) = $this->getQueryBuilder()->build($query);

        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals($expectedQueryParams, $actualQueryParams);
    }

    public function testOffset()
    {
        $expectedQuerySql = 'SELECT [id] FROM [example] ORDER BY (SELECT NULL) OFFSET 10 ROWS';
        $expectedQueryParams = [];

        $query = new Query();
        $query->select('id')->from('example')->offset(10);

        list($actualQuerySql, $actualQueryParams) = $this->getQueryBuilder()->build($query);

        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals($expectedQueryParams, $actualQueryParams);
    }
}
