<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use yii\base\InvalidParamException;

/**
 * QueryBuilder is the query builder for MS SQL Server databases (version 2008 and above).
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\db\QueryBuilder
{
	/**
	 * @var array mapping from abstract column types (keys) to physical column types (values).
	 */
	public $typeMap = [
		Schema::TYPE_PK => 'int IDENTITY PRIMARY KEY',
		Schema::TYPE_BIGPK => 'bigint IDENTITY PRIMARY KEY',
		Schema::TYPE_STRING => 'varchar(255)',
		Schema::TYPE_TEXT => 'text',
		Schema::TYPE_SMALLINT => 'smallint(6)',
		Schema::TYPE_INTEGER => 'int(11)',
		Schema::TYPE_BIGINT => 'bigint(20)',
		Schema::TYPE_FLOAT => 'float',
		Schema::TYPE_DECIMAL => 'decimal(10,0)',
		Schema::TYPE_DATETIME => 'datetime',
		Schema::TYPE_TIMESTAMP => 'timestamp',
		Schema::TYPE_TIME => 'time',
		Schema::TYPE_DATE => 'date',
		Schema::TYPE_BINARY => 'binary',
		Schema::TYPE_BOOLEAN => 'tinyint(1)',
		Schema::TYPE_MONEY => 'decimal(19,4)',
	];

//	public function update($table, $columns, $condition, &$params)
//	{
//		return '';
//	}

//	public function delete($table, $condition, &$params)
//	{
//		return '';
//	}

//	public function buildLimit($limit, $offset)
//	{
//		return '';
//	}

//	public function resetSequence($table, $value = null)
//	{
//		return '';
//	}

	/**
	 * Builds a SQL statement for enabling or disabling integrity check.
	 * @param boolean $check whether to turn on or off the integrity check.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * @param string $table the table name. Defaults to empty string, meaning that no table will be changed.
	 * @return string the SQL statement for checking integrity
	 * @throws InvalidParamException if the table does not exist or there is no sequence associated with the table.
	 */
	public function checkIntegrity($check = true, $schema = '', $table = '')
	{
		if ($schema !== '') {
			$table = "{$schema}.{$table}";
		}
		$table = $this->db->quoteTableName($table);
		if ($this->db->getTableSchema($table) === null) {
			throw new InvalidParamException("Table not found: $table");
		}
		$enable = $check ? 'CHECK' : 'NOCHECK';
		return "ALTER TABLE {$table} {$enable} CONSTRAINT ALL";
	}
}
