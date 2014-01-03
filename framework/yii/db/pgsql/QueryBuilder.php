<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

use yii\base\InvalidParamException;

/**
 * QueryBuilder is the query builder for PostgreSQL databases.
 *
 * @author Gevik Babakhani <gevikb@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\db\QueryBuilder
{

	/**
	 * @var array mapping from abstract column types (keys) to physical column types (values).
	 */
	public $typeMap = [
		Schema::TYPE_PK => 'serial NOT NULL PRIMARY KEY',
		Schema::TYPE_BIGPK => 'bigserial NOT NULL PRIMARY KEY',
		Schema::TYPE_STRING => 'varchar(255)',
		Schema::TYPE_TEXT => 'text',
		Schema::TYPE_SMALLINT => 'smallint',
		Schema::TYPE_INTEGER => 'integer',
		Schema::TYPE_BIGINT => 'bigint',
		Schema::TYPE_FLOAT => 'double precision',
		Schema::TYPE_DECIMAL => 'numeric(10,0)',
		Schema::TYPE_DATETIME => 'timestamp',
		Schema::TYPE_TIMESTAMP => 'timestamp',
		Schema::TYPE_TIME => 'time',
		Schema::TYPE_DATE => 'date',
		Schema::TYPE_BINARY => 'bytea',
		Schema::TYPE_BOOLEAN => 'boolean',
		Schema::TYPE_MONEY => 'numeric(19,4)',
	];

	/**
	 * Builds a SQL statement for dropping an index.
	 * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping an index.
	 */
	public function dropIndex($name, $table)
	{
		return 'DROP INDEX ' . $this->db->quoteTableName($name);
	}

	/**
	 * Builds a SQL statement for renaming a DB table.
	 * @param string $oldName the table to be renamed. The name will be properly quoted by the method.
	 * @param string $newName the new table name. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB table.
	 */
	public function renameTable($oldName, $newName)
	{
		return 'ALTER TABLE ' . $this->db->quoteTableName($oldName) . ' RENAME TO ' . $this->db->quoteTableName($newName);
	}

	/**
	 * Creates a SQL statement for resetting the sequence value of a table's primary key.
	 * The sequence will be reset such that the primary key of the next new row inserted
	 * will have the specified value or 1.
	 * @param string $tableName the name of the table whose primary key sequence will be reset
	 * @param mixed $value the value for the primary key of the next new row inserted. If this is not set,
	 * the next new row's primary key will have a value 1.
	 * @return string the SQL statement for resetting sequence
	 * @throws InvalidParamException if the table does not exist or there is no sequence associated with the table.
	 */
	public function resetSequence($tableName, $value = null)
	{
		$table = $this->db->getTableSchema($tableName);
		if ($table !== null && $table->sequenceName !== null) {
			$sequence = '"' . $table->sequenceName . '"';

			if (strpos($sequence, '.') !== false) {
				$sequence = str_replace('.', '"."', $sequence);
			}

			$tableName = $this->db->quoteTableName($tableName);
			if ($value === null) {
				$key = reset($table->primaryKey);
				$value = "(SELECT COALESCE(MAX(\"{$key}\"),0) FROM {$tableName})+1";
			} else {
				$value = (int)$value;
			}
			return "SELECT SETVAL('$sequence',$value,false)";
		} elseif ($table === null) {
			throw new InvalidParamException("Table not found: $tableName");
		} else {
			throw new InvalidParamException("There is not sequence associated with table '$tableName'.");
		}
	}

	/**
	 * Builds a SQL statement for enabling or disabling integrity check.
	 * @param boolean $check whether to turn on or off the integrity check.
	 * @param string $schema the schema of the tables.
	 * @param string $table the table name.
	 * @return string the SQL statement for checking integrity
	 */
	public function checkIntegrity($check = true, $schema = '', $table = '')
	{
		$enable = $check ? 'ENABLE' : 'DISABLE';
		$schema = $schema ? $schema : $this->db->schema->defaultSchema;
		$tableNames = $table ? [$table] : $this->db->schema->getTableNames($schema);
		$command = '';

		foreach ($tableNames as $tableName) {
			$tableName = '"' . $schema . '"."' . $tableName . '"';
			$command .= "ALTER TABLE $tableName $enable TRIGGER ALL; ";
		}

		#enable to have ability to alter several tables
		$this->db->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
		return $command;
	}

	/**
	 * Builds a SQL statement for changing the definition of a column.
	 * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
	 * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
	 * @param string $type the new column type. The [[getColumnType()]] method will be invoked to convert abstract
	 * column type (if any) into the physical one. Anything that is not recognized as abstract type will be kept
	 * in the generated SQL. For example, 'string' will be turned into 'varchar(255)', while 'string not null'
	 * will become 'varchar(255) not null'.
	 * @return string the SQL statement for changing the definition of a column.
	 */
	public function alterColumn($table, $column, $type)
	{
		return 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' ALTER COLUMN '
		. $this->db->quoteColumnName($column) . ' TYPE '
		. $this->getColumnType($type);
	}
}
