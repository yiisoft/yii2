<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * The BatchInsert trait provides [[batchInsert()]] method for MySQL and CUBRID databases.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @author Panagiotis Moustafellos <pmoust@gmail.com>
 * @since 2.0
 */
trait BatchInsertTrait
{
	/**
	 * Generates a batch INSERT SQL statement.
	 * For example,
	 *
	 * ~~~
	 * $connection->createCommand()->batchInsert('tbl_user', ['name', 'age'], [
	 *     ['Tom', 30],
	 *     ['Jane', 20],
	 *     ['Linda', 25],
	 * ])->execute();
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
			$columnSchemas = [];
		}

		foreach ($columns as $i => $name) {
			$columns[$i] = $this->db->quoteColumnName($name);
		}

		$values = [];
		foreach ($rows as $row) {
			$vs = [];
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