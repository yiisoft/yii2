<?php

namespace yiiunit\framework\db;

use yii\db\QueryBuilder;
use yii\db\Schema;
use yii\db\mysql\QueryBuilder as MysqlQueryBuilder;
use yii\db\sqlite\QueryBuilder as SqliteQueryBuilder;
use yii\db\mssql\QueryBuilder as MssqlQueryBuilder;
use yii\db\pgsql\QueryBuilder as PgsqlQueryBuilder;

class QueryBuilderTest extends DatabaseTestCase
{
	protected function setUp()
	{
		parent::setUp();
		$this->mockApplication();
	}

	/**
	 * @throws \Exception
	 * @return QueryBuilder
	 */
	protected function getQueryBuilder()
	{
		switch ($this->driverName) {
			case 'mysql':
				return new MysqlQueryBuilder($this->getConnection());
			case 'sqlite':
				return new SqliteQueryBuilder($this->getConnection());
			case 'mssql':
				return new MssqlQueryBuilder($this->getConnection());
			case 'pgsql':
				return new PgsqlQueryBuilder($this->getConnection());
		}
		throw new \Exception('Test is not implemented for ' . $this->driverName);
	}

	/**
	 * this is not used as a dataprovider for testGetColumnType to speed up the test
	 * when used as dataprovider every single line will cause a reconnect with the database which is not needed here
	 */
	public function columnTypes()
	{
		return array(
			array(Schema::TYPE_PK, 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY'),
			array(Schema::TYPE_PK . '(8)', 'int(8) NOT NULL AUTO_INCREMENT PRIMARY KEY'),
			array(Schema::TYPE_PK . ' CHECK (value > 5)', 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY CHECK (value > 5)'),
			array(Schema::TYPE_PK . '(8) CHECK (value > 5)', 'int(8) NOT NULL AUTO_INCREMENT PRIMARY KEY CHECK (value > 5)'),
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
			array(Schema::TYPE_SMALLINT, 'smallint(6)'),
			array(Schema::TYPE_SMALLINT . '(8)', 'smallint(8)'),
			array(Schema::TYPE_INTEGER, 'int(11)'),
			array(Schema::TYPE_INTEGER . '(8)', 'int(8)'),
			array(Schema::TYPE_INTEGER . ' CHECK (value > 5)', 'int(11) CHECK (value > 5)'),
			array(Schema::TYPE_INTEGER . '(8) CHECK (value > 5)', 'int(8) CHECK (value > 5)'),
			array(Schema::TYPE_INTEGER . ' NOT NULL', 'int(11) NOT NULL'),
			array(Schema::TYPE_BIGINT, 'bigint(20)'),
			array(Schema::TYPE_BIGINT . '(8)', 'bigint(8)'),
			array(Schema::TYPE_BIGINT . ' CHECK (value > 5)', 'bigint(20) CHECK (value > 5)'),
			array(Schema::TYPE_BIGINT . '(8) CHECK (value > 5)', 'bigint(8) CHECK (value > 5)'),
			array(Schema::TYPE_BIGINT . ' NOT NULL', 'bigint(20) NOT NULL'),
			array(Schema::TYPE_FLOAT, 'float'),
			array(Schema::TYPE_FLOAT . '(16,5)', 'float'),
			array(Schema::TYPE_FLOAT . ' CHECK (value > 5.6)', 'float CHECK (value > 5.6)'),
			array(Schema::TYPE_FLOAT . '(16,5) CHECK (value > 5.6)', 'float CHECK (value > 5.6)'),
			array(Schema::TYPE_FLOAT . ' NOT NULL', 'float NOT NULL'),
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
			array(Schema::TYPE_BOOLEAN, 'tinyint(1)'),
			array(Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1', 'tinyint(1) NOT NULL DEFAULT 1'),
			array(Schema::TYPE_MONEY, 'decimal(19,4)'),
			array(Schema::TYPE_MONEY . '(16,2)', 'decimal(16,2)'),
			array(Schema::TYPE_MONEY . ' CHECK (value > 0.0)', 'decimal(19,4) CHECK (value > 0.0)'),
			array(Schema::TYPE_MONEY . '(16,2) CHECK (value > 0.0)', 'decimal(16,2) CHECK (value > 0.0)'),
			array(Schema::TYPE_MONEY . ' NOT NULL', 'decimal(19,4) NOT NULL'),
		);
	}

	public function testGetColumnType()
	{
		$qb = $this->getQueryBuilder();
		foreach ($this->columnTypes() as $item) {
			list ($column, $expected) = $item;
			$this->assertEquals($expected, $qb->getColumnType($column));
		}
	}

	public function testAddDropPrimaryKey()
	{
		$tableName = 'tbl_constraints';
		$pkeyName = $tableName . "_pkey";
		
		// ADD
		$qb = $this->getQueryBuilder();
		$qb->db->createCommand()->addPrimaryKey($pkeyName, $tableName, array('id'))->execute();
		$tableSchema = $qb->db->getSchema()->getTableSchema($tableName);
		$this->assertEquals(1, count($tableSchema->primaryKey));

		//DROP
		$qb->db->createCommand()->dropPrimaryKey($pkeyName, $tableName)->execute();
		$qb = $this->getQueryBuilder(); // resets the schema
		$tableSchema = $qb->db->getSchema()->getTableSchema($tableName);
		$this->assertEquals(0, count($tableSchema->primaryKey));
	}
}
