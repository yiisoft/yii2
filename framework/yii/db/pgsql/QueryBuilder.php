<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

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
	public $typeMap = array(
		Schema::TYPE_PK => 'serial not null primary key',
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
	);

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
