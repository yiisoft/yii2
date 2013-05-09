<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mysql;

use yii\db\Exception;
use yii\base\InvalidParamException;

/**
 * QueryBuilder is the query builder for MySQL databases.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\db\QueryBuilder
{
	/**
	 * @var array mapping from abstract column types (keys) to physical column types (values).
	 */
	public $typeMap = array(
		Schema::TYPE_PK => 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
		Schema::TYPE_STRING => 'varchar(255)',
		Schema::TYPE_TEXT => 'text',
		Schema::TYPE_SMALLINT => 'smallint(6)',
		Schema::TYPE_INTEGER => 'int(11)',
		Schema::TYPE_BIGINT => 'bigint(20)',
		Schema::TYPE_FLOAT => 'float',
		Schema::TYPE_DECIMAL => 'decimal',
		Schema::TYPE_DATETIME => 'datetime',
		Schema::TYPE_TIMESTAMP => 'timestamp',
		Schema::TYPE_TIME => 'time',
		Schema::TYPE_DATE => 'date',
		Schema::TYPE_BINARY => 'blob',
		Schema::TYPE_BOOLEAN => 'tinyint(1)',
		Schema::TYPE_MONEY => 'decimal(19,4)',
	);

	/**
	 * Builds a SQL statement for renaming a column.
	 * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
	 * @param string $oldName the old name of the column. The name will be properly quoted by the method.
	 * @param string $newName the new name of the column. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB column.
	 * @throws Exception
	 */
	public function renameColumn($table, $oldName, $newName)
	{
		$quotedTable = $this->db->quoteTableName($table);
		$row = $this->db->createCommand('SHOW CREATE TABLE ' . $quotedTable)->queryRow();
		if ($row === false) {
			throw new Exception("Unable to find column '$oldName' in table '$table'.");
		}
		if (isset($row['Create Table'])) {
			$sql = $row['Create Table'];
		} else {
			$row = array_values($row);
			$sql = $row[1];
		}
		if (preg_match_all('/^\s*`(.*?)`\s+(.*?),?$/m', $sql, $matches)) {
			foreach ($matches[1] as $i => $c) {
				if ($c === $oldName) {
					return "ALTER TABLE $quotedTable CHANGE "
						. $this->db->quoteColumnName($oldName) . ' '
						. $this->db->quoteColumnName($newName) . ' '
						. $matches[2][$i];
				}
			}
		}
		// try to give back a SQL anyway
		return "ALTER TABLE $quotedTable CHANGE "
			. $this->db->quoteColumnName($oldName) . ' '
			. $this->db->quoteColumnName($newName);
	}

	/**
	 * Builds a SQL statement for dropping a foreign key constraint.
	 * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping a foreign key constraint.
	 */
	public function dropForeignKey($name, $table)
	{
		return 'ALTER TABLE ' . $this->db->quoteTableName($table)
			. ' DROP FOREIGN KEY ' . $this->db->quoteColumnName($name);
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
			$tableName = $this->db->quoteTableName($tableName);
			if ($value === null) {
				$key = reset($table->primaryKey);
				$value = $this->db->createCommand("SELECT MAX(`$key`) FROM $tableName")->queryScalar() + 1;
			} else {
				$value = (int)$value;
			}
			return "ALTER TABLE $tableName AUTO_INCREMENT=$value";
		} elseif ($table === null) {
			throw new InvalidParamException("Table not found: $tableName");
		} else {
			throw new InvalidParamException("There is not sequence associated with table '$tableName'.'");
		}
	}

	/**
	 * Builds a SQL statement for enabling or disabling integrity check.
	 * @param boolean $check whether to turn on or off the integrity check.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * @return string the SQL statement for checking integrity
	 */
	public function checkIntegrity($check = true, $schema = '')
	{
		return 'SET FOREIGN_KEY_CHECKS=' . ($check ? 1 : 0);
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
	 * Not that the values in each row must match the corresponding column names.
	 *
	 * @param string $table the table that new rows will be inserted into.
	 * @param array $columns the column names
	 * @param array $rows the rows to be batch inserted into the table
	 * @return string the batch INSERT SQL statement
	 */
	public function batchInsert($table, $columns, $rows)
	{
		$values = array();
		foreach ($rows as $row) {
			$vs = array();
			foreach ($row as $value) {
				$vs[] = is_string($value) ? $this->db->quoteValue($value) : $value;
			}
			$values[] = $vs;
		}

		return 'INSERT INTO ' . $this->db->quoteTableName($table)
			. ' (' . implode(', ', $columns) . ') VALUES ('
			. implode(', ', $values) . ')';
	}
}
