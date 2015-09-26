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
            [Schema::TYPE_PK, 'int IDENTITY PRIMARY KEY'],
            [Schema::TYPE_PK . '(8)', 'int IDENTITY PRIMARY KEY'],
            [Schema::TYPE_PK . ' CHECK (value > 5)', 'int IDENTITY PRIMARY KEY CHECK (value > 5)'],
            [Schema::TYPE_PK . '(8) CHECK (value > 5)', 'int IDENTITY PRIMARY KEY CHECK (value > 5)'],
            [Schema::TYPE_STRING, 'varchar(255)'],
            [Schema::TYPE_STRING . '(32)', 'varchar(32)'],
            [Schema::TYPE_STRING . ' CHECK (value LIKE \'test%\')', 'varchar(255) CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_STRING . '(32) CHECK (value LIKE \'test%\')', 'varchar(32) CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_STRING . ' NOT NULL', 'varchar(255) NOT NULL'],
            [Schema::TYPE_TEXT, 'text'],
            [Schema::TYPE_TEXT . '(255)', 'text'],
            [Schema::TYPE_TEXT . ' NOT NULL', 'text NOT NULL'],
            [Schema::TYPE_TEXT . '(255) NOT NULL', 'text NOT NULL'],
            [Schema::TYPE_SMALLINT, 'smallint'],
            [Schema::TYPE_SMALLINT . '(8)', 'smallint'],
            [Schema::TYPE_INTEGER, 'int'],
            [Schema::TYPE_INTEGER . '(8)', 'int'],
            [Schema::TYPE_INTEGER . ' CHECK (value > 5)', 'int CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . '(8) CHECK (value > 5)', 'int CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . ' NOT NULL', 'int NOT NULL'],
            [Schema::TYPE_BIGINT, 'bigint'],
            [Schema::TYPE_BIGINT . '(8)', 'bigint'],
            [Schema::TYPE_BIGINT . ' CHECK (value > 5)', 'bigint CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . '(8) CHECK (value > 5)', 'bigint CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . ' NOT NULL', 'bigint NOT NULL'],
            [Schema::TYPE_FLOAT, 'float'],
            [Schema::TYPE_FLOAT . '(16,5)', 'float'],
            [Schema::TYPE_FLOAT . ' CHECK (value > 5.6)', 'float CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . '(16,5) CHECK (value > 5.6)', 'float CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . ' NOT NULL', 'float NOT NULL'],
            [Schema::TYPE_DOUBLE, 'float'],
            [Schema::TYPE_DOUBLE . '(16,5)', 'float'],
            [Schema::TYPE_DOUBLE . ' CHECK (value > 5.6)', 'float CHECK (value > 5.6)'],
            [Schema::TYPE_DOUBLE . '(16,5) CHECK (value > 5.6)', 'float CHECK (value > 5.6)'],
            [Schema::TYPE_DOUBLE . ' NOT NULL', 'float NOT NULL'],
            [Schema::TYPE_DECIMAL, 'decimal'],
            [Schema::TYPE_DECIMAL . '(12,4)', 'decimal'],
            [Schema::TYPE_DECIMAL . ' CHECK (value > 5.6)', 'decimal CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . '(12,4) CHECK (value > 5.6)', 'decimal CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . ' NOT NULL', 'decimal NOT NULL'],
            [Schema::TYPE_DATETIME, 'datetime'],
            [Schema::TYPE_DATETIME . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", "datetime CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATETIME . ' NOT NULL', 'datetime NOT NULL'],
            [Schema::TYPE_TIMESTAMP, 'timestamp'],
            [Schema::TYPE_TIME, 'time'],
            [Schema::TYPE_TIME . " CHECK (value BETWEEN '12:00:00' AND '13:01:01')", "time CHECK (value BETWEEN '12:00:00' AND '13:01:01')"],
            [Schema::TYPE_TIME . ' NOT NULL', 'time NOT NULL'],
            [Schema::TYPE_DATE, 'date'],
            [Schema::TYPE_DATE . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", "date CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATE . ' NOT NULL', 'date NOT NULL'],
            [Schema::TYPE_BINARY, 'binary(1)'],
            [Schema::TYPE_BOOLEAN, 'bit'],
            [Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1', 'bit NOT NULL DEFAULT 1'],
            [Schema::TYPE_MONEY, 'decimal(19,4)'],
            [Schema::TYPE_MONEY . '(16,2)', 'decimal(16,2)'],
            [Schema::TYPE_MONEY . ' CHECK (value > 0.0)', 'decimal(19,4) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . '(16,2) CHECK (value > 0.0)', 'decimal(16,2) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . ' NOT NULL', 'decimal(19,4) NOT NULL'],
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
