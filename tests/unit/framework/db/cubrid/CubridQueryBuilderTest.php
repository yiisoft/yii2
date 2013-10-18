<?php

namespace yiiunit\framework\db\cubrid;

use yii\base\NotSupportedException;
use yii\db\sqlite\Schema;
use yiiunit\framework\db\QueryBuilderTest;

/**
 * @group db
 * @group cubrid
 */
class CubridQueryBuilderTest extends QueryBuilderTest
{
	public $driverName = 'cubrid';

	/**
	 * this is not used as a dataprovider for testGetColumnType to speed up the test
	 * when used as dataprovider every single line will cause a reconnect with the database which is not needed here
	 */
	public function columnTypes()
	{
		return [
			[Schema::TYPE_PK, 'int NOT NULL AUTO_INCREMENT PRIMARY KEY'],
			[Schema::TYPE_PK . '(8)', 'int NOT NULL AUTO_INCREMENT PRIMARY KEY'],
			[Schema::TYPE_PK . ' CHECK (value > 5)', 'int NOT NULL AUTO_INCREMENT PRIMARY KEY CHECK (value > 5)'],
			[Schema::TYPE_PK . '(8) CHECK (value > 5)', 'int NOT NULL AUTO_INCREMENT PRIMARY KEY CHECK (value > 5)'],
			[Schema::TYPE_STRING, 'varchar(255)'],
			[Schema::TYPE_STRING . '(32)', 'varchar(32)'],
			[Schema::TYPE_STRING . ' CHECK (value LIKE "test%")', 'varchar(255) CHECK (value LIKE "test%")'],
			[Schema::TYPE_STRING . '(32) CHECK (value LIKE "test%")', 'varchar(32) CHECK (value LIKE "test%")'],
			[Schema::TYPE_STRING . ' NOT NULL', 'varchar(255) NOT NULL'],
			[Schema::TYPE_TEXT, 'varchar'],
			[Schema::TYPE_TEXT . '(255)', 'varchar'],
			[Schema::TYPE_TEXT . ' CHECK (value LIKE "test%")', 'varchar CHECK (value LIKE "test%")'],
			[Schema::TYPE_TEXT . '(255) CHECK (value LIKE "test%")', 'varchar CHECK (value LIKE "test%")'],
			[Schema::TYPE_TEXT . ' NOT NULL', 'varchar NOT NULL'],
			[Schema::TYPE_TEXT . '(255) NOT NULL', 'varchar NOT NULL'],
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
			[Schema::TYPE_FLOAT, 'float(7)'],
			[Schema::TYPE_FLOAT . '(16)', 'float(16)'],
			[Schema::TYPE_FLOAT . ' CHECK (value > 5.6)', 'float(7) CHECK (value > 5.6)'],
			[Schema::TYPE_FLOAT . '(16) CHECK (value > 5.6)', 'float(16) CHECK (value > 5.6)'],
			[Schema::TYPE_FLOAT . ' NOT NULL', 'float(7) NOT NULL'],
			[Schema::TYPE_DECIMAL, 'decimal(10,0)'],
			[Schema::TYPE_DECIMAL . '(12,4)', 'decimal(12,4)'],
			[Schema::TYPE_DECIMAL . ' CHECK (value > 5.6)', 'decimal(10,0) CHECK (value > 5.6)'],
			[Schema::TYPE_DECIMAL . '(12,4) CHECK (value > 5.6)', 'decimal(12,4) CHECK (value > 5.6)'],
			[Schema::TYPE_DECIMAL . ' NOT NULL', 'decimal(10,0) NOT NULL'],
			[Schema::TYPE_DATETIME, 'datetime'],
			[Schema::TYPE_DATETIME . " CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')", "datetime CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')"],
			[Schema::TYPE_DATETIME . ' NOT NULL', 'datetime NOT NULL'],
			[Schema::TYPE_TIMESTAMP, 'timestamp'],
			[Schema::TYPE_TIMESTAMP . " CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')", "timestamp CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')"],
			[Schema::TYPE_TIMESTAMP . ' NOT NULL', 'timestamp NOT NULL'],
			[Schema::TYPE_TIME, 'time'],
			[Schema::TYPE_TIME . " CHECK(value BETWEEN '12:00:00' AND '13:01:01')", "time CHECK(value BETWEEN '12:00:00' AND '13:01:01')"],
			[Schema::TYPE_TIME . ' NOT NULL', 'time NOT NULL'],
			[Schema::TYPE_DATE, 'date'],
			[Schema::TYPE_DATE . " CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')", "date CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')"],
			[Schema::TYPE_DATE . ' NOT NULL', 'date NOT NULL'],
			[Schema::TYPE_BINARY, 'blob'],
			[Schema::TYPE_BOOLEAN, 'smallint'],
			[Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1', 'smallint NOT NULL DEFAULT 1'],
			[Schema::TYPE_MONEY, 'decimal(19,4)'],
			[Schema::TYPE_MONEY . '(16,2)', 'decimal(16,2)'],
			[Schema::TYPE_MONEY . ' CHECK (value > 0.0)', 'decimal(19,4) CHECK (value > 0.0)'],
			[Schema::TYPE_MONEY . '(16,2) CHECK (value > 0.0)', 'decimal(16,2) CHECK (value > 0.0)'],
			[Schema::TYPE_MONEY . ' NOT NULL', 'decimal(19,4) NOT NULL'],
		];
	}

}
