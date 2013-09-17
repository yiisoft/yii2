<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\cubrid;

use yii\base\InvalidParamException;

/**
 * QueryBuilder is the query builder for CUBRID databases (version 9.1.x and higher).
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class QueryBuilder extends \yii\db\QueryBuilder
{
	/**
	 * @var array mapping from abstract column types (keys) to physical column types (values).
	 */
	public $typeMap = array(
		Schema::TYPE_PK => 'int NOT NULL AUTO_INCREMENT PRIMARY KEY',
		Schema::TYPE_BIGPK => 'bigint NOT NULL AUTO_INCREMENT PRIMARY KEY',
		Schema::TYPE_STRING => 'varchar(255)',
		Schema::TYPE_TEXT => 'varchar',
		Schema::TYPE_SMALLINT => 'smallint',
		Schema::TYPE_INTEGER => 'int',
		Schema::TYPE_BIGINT => 'bigint',
		Schema::TYPE_FLOAT => 'float(7)',
		Schema::TYPE_DECIMAL => 'decimal(10,0)',
		Schema::TYPE_DATETIME => 'datetime',
		Schema::TYPE_TIMESTAMP => 'timestamp',
		Schema::TYPE_TIME => 'time',
		Schema::TYPE_DATE => 'date',
		Schema::TYPE_BINARY => 'blob',
		Schema::TYPE_BOOLEAN => 'smallint',
		Schema::TYPE_MONEY => 'decimal(19,4)',
	);

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
			$tableName = $this->db->quoteTableName($tableName);
			if ($value === null) {
				$key = reset($table->primaryKey);
				$value = (int)$this->db->createCommand("SELECT MAX(`$key`) FROM " . $this->db->schema->quoteTableName($tableName))->queryScalar() + 1;
			} else {
				$value = (int)$value;
			}
			return "ALTER TABLE " . $this->db->schema->quoteTableName($tableName) . " AUTO_INCREMENT=$value;";
		} elseif ($table === null) {
			throw new InvalidParamException("Table not found: $tableName");
		} else {
			throw new InvalidParamException("There is not sequence associated with table '$tableName'.");
		}
	}

	/**
	 * Generates a batch INSERT SQL statement.
	 * For example,
	 *
	 * ~~~
	 * $connection->createCommand()->batchInsert('tbl_user', array('name', 'age'), array(
	 *     array('Tom', 30),
	 *     array('Jane', 20),
	 *     array('Linda', 25),
	 * ))->execute();
	 * ~~~
	 *
	 * Note that the values in each row must match the corresponding column names.
	 *
	 * @param string $table the table that new rows will be inserted into.
	 * @param array $columns the column names
	 * @param array $rows the rows to be batch inserted into the table
	 * @return string the batch INSERT SQL statement
	 */
	public function batchInsert($table, $columns, $rows)
	{
		if (($tableSchema = $this->db->getTableSchema($table)) !== null) {
			$columnSchemas = $tableSchema->columns;
		} else {
			$columnSchemas = array();
		}

		foreach ($columns as $i => $name) {
			$columns[$i] = $this->db->quoteColumnName($name);
		}

		$values = array();
		foreach ($rows as $row) {
			$vs = array();
			foreach ($row as $i => $value) {
				if (!is_array($value) && isset($columnSchemas[$columns[$i]])) {
					$value = $columnSchemas[$columns[$i]]->typecast($value);
				}
				$vs[] = is_string($value) ? $this->db->quoteValue($value) : $value;
			}
			$values[] = '(' . implode(', ', $vs) . ')';
		}

		return 'INSERT INTO ' . $this->db->quoteTableName($table)
			. ' (' . implode(', ', $columns) . ') VALUES ' . implode(', ', $values);
	}
}
