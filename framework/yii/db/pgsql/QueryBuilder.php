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
	 * Creates an INSERT SQL statement.
	 * For example,
	 *
	 * ~~~
	 * $sql = $queryBuilder->insert('tbl_user', [
	 *     'name' => 'Sam',
	 *     'age' => 30,
	 * ], $params);
	 * ~~~
	 *
	 * The method will properly escape the table and column names.
	 *
	 * @param string $table the table that new rows will be inserted into.
	 * @param array $columns the column data (name => value) to be inserted into the table.
	 * @param array $params the binding parameters that will be generated by this method.
	 * They should be bound to the DB command later.
	 * @return string the INSERT SQL
	 */
	public function insert($table, $columns, &$params)
	{
		if (($tableSchema = $this->db->getTableSchema($table)) !== null) {
			$columnSchemas = $tableSchema->columns;
		} else {
			$columnSchemas = [];
		}
		$names = [];
		$placeholders = [];
		foreach ($columns as $name => $value) {
			$names[] = $this->db->quoteColumnName($name);
			if ($value instanceof Expression) {
				$placeholders[] = $value->expression;
				foreach ($value->params as $n => $v) {
					$params[$n] = is_bool($v) ? (int)$v : $params[$n] = $v;
				}
			} else {
				$phName = self::PARAM_PREFIX . count($params);
				$placeholders[] = $phName;
				$params[$phName] = !is_array($value) && isset($columnSchemas[$name]) ? $columnSchemas[$name]->typecast($value) : $value;
				$params[$phName] = is_bool($params[$phName]) ? (int)$params[$phName] : $params[$phName];
			}
		}

		return 'INSERT INTO ' . $this->db->quoteTableName($table)
		. ' (' . implode(', ', $names) . ') VALUES ('
		. implode(', ', $placeholders) . ')';
	}

	/**
	 * Creates an UPDATE SQL statement.
	 * For example,
	 *
	 * ~~~
	 * $params = [];
	 * $sql = $queryBuilder->update('tbl_user', ['status' => 1], 'age > 30', $params);
	 * ~~~
	 *
	 * The method will properly escape the table and column names.
	 *
	 * @param string $table the table to be updated.
	 * @param array $columns the column data (name => value) to be updated.
	 * @param array|string $condition the condition that will be put in the WHERE part. Please
	 * refer to [[Query::where()]] on how to specify condition.
	 * @param array $params the binding parameters that will be modified by this method
	 * so that they can be bound to the DB command later.
	 * @return string the UPDATE SQL
	 */
	public function update($table, $columns, $condition, &$params)
	{
		if (($tableSchema = $this->db->getTableSchema($table)) !== null) {
			$columnSchemas = $tableSchema->columns;
		} else {
			$columnSchemas = [];
		}

		$lines = [];
		foreach ($columns as $name => $value) {
			if ($value instanceof Expression) {
				$lines[] = $this->db->quoteColumnName($name) . '=' . $value->expression;
				foreach ($value->params as $n => $v) {
					$params[$n] = is_bool($v) ? (int)$v : $params[$n] = $v;
				}
			} else {
				$phName = self::PARAM_PREFIX . count($params);
				$lines[] = $this->db->quoteColumnName($name) . '=' . $phName;
				$params[$phName] = !is_array($value) && isset($columnSchemas[$name]) ? $columnSchemas[$name]->typecast($value) : $value;
				$params[$phName] = is_bool($params[$phName]) ? (int)$params[$phName] : $params[$phName];
			}
		}

		$sql = 'UPDATE ' . $this->db->quoteTableName($table) . ' SET ' . implode(', ', $lines);
		$where = $this->buildWhere($condition, $params);
		return $where === '' ? $sql : $sql . ' ' . $where;
	}

	/**
	 * Parses the condition specification and generates the corresponding SQL expression.
	 *
	 * @param string|array $condition the condition specification. Please refer to [[Query::where()]]
	 * on how to specify a condition.
	 * @param array $params the binding parameters to be populated
	 * @return string the generated SQL expression
	 * @throws \yii\db\Exception if the condition is in bad format
	 */
	public function buildCondition($condition, &$params)
	{
		foreach ($params as $k => $v) {
			$params[$k] = is_bool($v) ? (int)$v : $v;
		}
		parent::buildCondition($condition, $params);
	}

	/**
	 * Builds a SQL statement for dropping an index.
	 *
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
	 *
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
	 *
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
