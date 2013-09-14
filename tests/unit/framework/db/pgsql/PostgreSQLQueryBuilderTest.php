<?php

namespace yiiunit\framework\db\pgsql;

use yii\db\pgsql\Schema;
use yiiunit\framework\db\QueryBuilderTest;

class PostgreSQLQueryBuilderTest extends QueryBuilderTest
{
	public $driverName = 'pgsql';
	
	public function columnTypes()
	{
		return array(
			array(Schema::TYPE_PK, 'serial NOT NULL PRIMARY KEY'),
			array(Schema::TYPE_PK . '(8)', 'serial NOT NULL PRIMARY KEY'),
			array(Schema::TYPE_PK . ' CHECK (value > 5)', 'serial NOT NULL PRIMARY KEY CHECK (value > 5)'),
			array(Schema::TYPE_PK . '(8) CHECK (value > 5)', 'serial NOT NULL PRIMARY KEY CHECK (value > 5)'),
			array(Schema::TYPE_STRING, 'varchar(255)'),
			array(Schema::TYPE_STRING . '(32)', 'varchar(32)'),
			array(Schema::TYPE_STRING . ' CHECK (value LIKE "test%")', 'varchar(255) CHECK (value LIKE "test%")'),
			array(Schema::TYPE_STRING . '(32) CHECK (value LIKE "test%")', 'varchar(32) CHECK (value LIKE "test%")'),
			array(Schema::TYPE_STRING . ' NOT NULL', 'varchar(255) NOT NULL'),
			array(Schema::TYPE_TEXT, 'text'),
			array(Schema::TYPE_TEXT . '(255)', 'text'),
			array(Schema::TYPE_TEXT . ' CHECK (value LIKE "test%")', 'text CHECK (value LIKE "test%")'),
			array(Schema::TYPE_TEXT . '(255) CHECK (value LIKE "test%")', 'text CHECK (value LIKE "test%")'),
			array(Schema::TYPE_TEXT . ' NOT NULL', 'text NOT NULL'),
			array(Schema::TYPE_TEXT . '(255) NOT NULL', 'text NOT NULL'),
			array(Schema::TYPE_SMALLINT, 'smallint'),
			array(Schema::TYPE_SMALLINT . '(8)', 'smallint'),
			array(Schema::TYPE_INTEGER, 'integer'),
			array(Schema::TYPE_INTEGER . '(8)', 'integer'),
			array(Schema::TYPE_INTEGER . ' CHECK (value > 5)', 'integer CHECK (value > 5)'),
			array(Schema::TYPE_INTEGER . '(8) CHECK (value > 5)', 'integer CHECK (value > 5)'),
			array(Schema::TYPE_INTEGER . ' NOT NULL', 'integer NOT NULL'),
			array(Schema::TYPE_BIGINT, 'bigint'),
			array(Schema::TYPE_BIGINT . '(8)', 'bigint'),
			array(Schema::TYPE_BIGINT . ' CHECK (value > 5)', 'bigint CHECK (value > 5)'),
			array(Schema::TYPE_BIGINT . '(8) CHECK (value > 5)', 'bigint CHECK (value > 5)'),
			array(Schema::TYPE_BIGINT . ' NOT NULL', 'bigint NOT NULL'),
			array(Schema::TYPE_FLOAT, 'double precision'),
			array(Schema::TYPE_FLOAT . ' CHECK (value > 5.6)', 'double precision CHECK (value > 5.6)'),
			array(Schema::TYPE_FLOAT . '(16,5) CHECK (value > 5.6)', 'double precision CHECK (value > 5.6)'),
			array(Schema::TYPE_FLOAT . ' NOT NULL', 'double precision NOT NULL'),
			array(Schema::TYPE_DECIMAL, 'numeric(10,0)'),
			array(Schema::TYPE_DECIMAL . '(12,4)', 'numeric(12,4)'),
			array(Schema::TYPE_DECIMAL . ' CHECK (value > 5.6)', 'numeric(10,0) CHECK (value > 5.6)'),
			array(Schema::TYPE_DECIMAL . '(12,4) CHECK (value > 5.6)', 'numeric(12,4) CHECK (value > 5.6)'),
			array(Schema::TYPE_DECIMAL . ' NOT NULL', 'numeric(10,0) NOT NULL'),
			array(Schema::TYPE_DATETIME, 'timestamp'),
			array(Schema::TYPE_DATETIME . " CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')", "timestamp CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')"),
			array(Schema::TYPE_DATETIME . ' NOT NULL', 'timestamp NOT NULL'),
			array(Schema::TYPE_TIMESTAMP, 'timestamp'),
			array(Schema::TYPE_TIMESTAMP . " CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')", "timestamp CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')"),
			array(Schema::TYPE_TIMESTAMP . ' NOT NULL', 'timestamp NOT NULL'),
			array(Schema::TYPE_TIME, 'time'),
			array(Schema::TYPE_TIME . " CHECK(value BETWEEN '12:00:00' AND '13:01:01')", "time CHECK(value BETWEEN '12:00:00' AND '13:01:01')"),
			array(Schema::TYPE_TIME . ' NOT NULL', 'time NOT NULL'),
			array(Schema::TYPE_DATE, 'date'),
			array(Schema::TYPE_DATE . " CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')", "date CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')"),
			array(Schema::TYPE_DATE . ' NOT NULL', 'date NOT NULL'),
			array(Schema::TYPE_BINARY, 'bytea'),
			array(Schema::TYPE_BOOLEAN, 'boolean'),
			array(Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1', 'boolean NOT NULL DEFAULT 1'),
			array(Schema::TYPE_MONEY, 'numeric(19,4)'),
			array(Schema::TYPE_MONEY . '(16,2)', 'numeric(16,2)'),
			array(Schema::TYPE_MONEY . ' CHECK (value > 0.0)', 'numeric(19,4) CHECK (value > 0.0)'),
			array(Schema::TYPE_MONEY . '(16,2) CHECK (value > 0.0)', 'numeric(16,2) CHECK (value > 0.0)'),
			array(Schema::TYPE_MONEY . ' NOT NULL', 'numeric(19,4) NOT NULL'),
		);
	}
}
