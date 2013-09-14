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
		return array(
			array(Schema::TYPE_PK, 'int NOT NULL AUTO_INCREMENT PRIMARY KEY'),
			array(Schema::TYPE_PK . '(8)', 'int NOT NULL AUTO_INCREMENT PRIMARY KEY'),
			array(Schema::TYPE_PK . ' CHECK (value > 5)', 'int NOT NULL AUTO_INCREMENT PRIMARY KEY CHECK (value > 5)'),
			array(Schema::TYPE_PK . '(8) CHECK (value > 5)', 'int NOT NULL AUTO_INCREMENT PRIMARY KEY CHECK (value > 5)'),
			array(Schema::TYPE_STRING, 'varchar(255)'),
			array(Schema::TYPE_STRING . '(32)', 'varchar(32)'),
			array(Schema::TYPE_STRING . ' CHECK (value LIKE "test%")', 'varchar(255) CHECK (value LIKE "test%")'),
			array(Schema::TYPE_STRING . '(32) CHECK (value LIKE "test%")', 'varchar(32) CHECK (value LIKE "test%")'),
			array(Schema::TYPE_STRING . ' NOT NULL', 'varchar(255) NOT NULL'),
			array(Schema::TYPE_TEXT, 'varchar'),
			array(Schema::TYPE_TEXT . '(255)', 'varchar'),
			array(Schema::TYPE_TEXT . ' CHECK (value LIKE "test%")', 'varchar CHECK (value LIKE "test%")'),
			array(Schema::TYPE_TEXT . '(255) CHECK (value LIKE "test%")', 'varchar CHECK (value LIKE "test%")'),
			array(Schema::TYPE_TEXT . ' NOT NULL', 'varchar NOT NULL'),
			array(Schema::TYPE_TEXT . '(255) NOT NULL', 'varchar NOT NULL'),
			array(Schema::TYPE_SMALLINT, 'smallint'),
			array(Schema::TYPE_SMALLINT . '(8)', 'smallint'),
			array(Schema::TYPE_INTEGER, 'int'),
			array(Schema::TYPE_INTEGER . '(8)', 'int'),
			array(Schema::TYPE_INTEGER . ' CHECK (value > 5)', 'int CHECK (value > 5)'),
			array(Schema::TYPE_INTEGER . '(8) CHECK (value > 5)', 'int CHECK (value > 5)'),
			array(Schema::TYPE_INTEGER . ' NOT NULL', 'int NOT NULL'),
			array(Schema::TYPE_BIGINT, 'bigint'),
			array(Schema::TYPE_BIGINT . '(8)', 'bigint'),
			array(Schema::TYPE_BIGINT . ' CHECK (value > 5)', 'bigint CHECK (value > 5)'),
			array(Schema::TYPE_BIGINT . '(8) CHECK (value > 5)', 'bigint CHECK (value > 5)'),
			array(Schema::TYPE_BIGINT . ' NOT NULL', 'bigint NOT NULL'),
			array(Schema::TYPE_FLOAT, 'float(7)'),
			array(Schema::TYPE_FLOAT . '(16)', 'float(16)'),
			array(Schema::TYPE_FLOAT . ' CHECK (value > 5.6)', 'float(7) CHECK (value > 5.6)'),
			array(Schema::TYPE_FLOAT . '(16) CHECK (value > 5.6)', 'float(16) CHECK (value > 5.6)'),
			array(Schema::TYPE_FLOAT . ' NOT NULL', 'float(7) NOT NULL'),
			array(Schema::TYPE_DECIMAL, 'decimal(10,0)'),
			array(Schema::TYPE_DECIMAL . '(12,4)', 'decimal(12,4)'),
			array(Schema::TYPE_DECIMAL . ' CHECK (value > 5.6)', 'decimal(10,0) CHECK (value > 5.6)'),
			array(Schema::TYPE_DECIMAL . '(12,4) CHECK (value > 5.6)', 'decimal(12,4) CHECK (value > 5.6)'),
			array(Schema::TYPE_DECIMAL . ' NOT NULL', 'decimal(10,0) NOT NULL'),
			array(Schema::TYPE_DATETIME, 'datetime'),
			array(Schema::TYPE_DATETIME . " CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')", "datetime CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')"),
			array(Schema::TYPE_DATETIME . ' NOT NULL', 'datetime NOT NULL'),
			array(Schema::TYPE_TIMESTAMP, 'timestamp'),
			array(Schema::TYPE_TIMESTAMP . " CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')", "timestamp CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')"),
			array(Schema::TYPE_TIMESTAMP . ' NOT NULL', 'timestamp NOT NULL'),
			array(Schema::TYPE_TIME, 'time'),
			array(Schema::TYPE_TIME . " CHECK(value BETWEEN '12:00:00' AND '13:01:01')", "time CHECK(value BETWEEN '12:00:00' AND '13:01:01')"),
			array(Schema::TYPE_TIME . ' NOT NULL', 'time NOT NULL'),
			array(Schema::TYPE_DATE, 'date'),
			array(Schema::TYPE_DATE . " CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')", "date CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')"),
			array(Schema::TYPE_DATE . ' NOT NULL', 'date NOT NULL'),
			array(Schema::TYPE_BINARY, 'blob'),
			array(Schema::TYPE_BOOLEAN, 'smallint'),
			array(Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1', 'smallint NOT NULL DEFAULT 1'),
			array(Schema::TYPE_MONEY, 'decimal(19,4)'),
			array(Schema::TYPE_MONEY . '(16,2)', 'decimal(16,2)'),
			array(Schema::TYPE_MONEY . ' CHECK (value > 0.0)', 'decimal(19,4) CHECK (value > 0.0)'),
			array(Schema::TYPE_MONEY . '(16,2) CHECK (value > 0.0)', 'decimal(16,2) CHECK (value > 0.0)'),
			array(Schema::TYPE_MONEY . ' NOT NULL', 'decimal(19,4) NOT NULL'),
		);
	}

}
