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
		Schema::TYPE_SMALLINT => 'smallint',
		Schema::TYPE_INTEGER => 'int',
		Schema::TYPE_BIGINT => 'bigint',
		Schema::TYPE_FLOAT => 'float',
		Schema::TYPE_DECIMAL => 'decimal',
		Schema::TYPE_DATETIME => 'datetime',
		Schema::TYPE_TIMESTAMP => 'timestamp',
		Schema::TYPE_TIME => 'time',
		Schema::TYPE_DATE => 'date',
		Schema::TYPE_BINARY => 'binary',
		Schema::TYPE_BOOLEAN => 'bit',
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
	 * Builds a SQL statement for renaming a DB table.
	 * @param string $table the table to be renamed. The name will be properly quoted by the method.
	 * @param string $newName the new table name. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB table.
	 */
	public function renameTable($table, $newName)
	{
		return "sp_rename '$table', '$newName'";
	}

	/**
	 * Builds a SQL statement for renaming a column.
	 * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
	 * @param string $name the old name of the column. The name will be properly quoted by the method.
	 * @param string $newName the new name of the column. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB column.
	 */
	public function renameColumn($table, $name, $newName)
	{
		return "sp_rename '$table.$name', '$newName', 'COLUMN'";
	}

	/**
	 * Builds a SQL statement for changing the definition of a column.
	 * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
	 * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
	 * @param string $type the new column type. The {@link getColumnType} method will be invoked to convert abstract column type (if any)
	 * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
	 * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
	 * @return string the SQL statement for changing the definition of a column.
	 */
	public function alterColumn($table, $column, $type)
	{
		$type=$this->getColumnType($type);
		$sql='ALTER TABLE ' . $this->db->quoteTableName($table) . ' ALTER COLUMN '
			. $this->db->quoteColumnName($column) . ' '
			. $this->getColumnType($type);
		return $sql;
	}

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
