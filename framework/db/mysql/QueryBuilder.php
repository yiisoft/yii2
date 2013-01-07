<?php
/**
 * QueryBuilder class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mysql;

use yii\db\Exception;

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
		Driver::TYPE_PK => 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
		Driver::TYPE_STRING => 'varchar(255)',
		Driver::TYPE_TEXT => 'text',
		Driver::TYPE_SMALLINT => 'smallint(6)',
		Driver::TYPE_INTEGER => 'int(11)',
		Driver::TYPE_BIGINT => 'bigint(20)',
		Driver::TYPE_FLOAT => 'float',
		Driver::TYPE_DECIMAL => 'decimal',
		Driver::TYPE_DATETIME => 'datetime',
		Driver::TYPE_TIMESTAMP => 'timestamp',
		Driver::TYPE_TIME => 'time',
		Driver::TYPE_DATE => 'date',
		Driver::TYPE_BINARY => 'blob',
		Driver::TYPE_BOOLEAN => 'tinyint(1)',
		Driver::TYPE_MONEY => 'decimal(19,4)',
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
		$quotedTable = $this->connection->quoteTableName($table);
		$row = $this->connection->createCommand('SHOW CREATE TABLE ' . $quotedTable)->queryRow();
		if ($row === false) {
			throw new Exception("Unable to find '$oldName' in table '$table'.");
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
						. $this->connection->quoteColumnName($oldName) . ' '
						. $this->connection->quoteColumnName($newName) . ' '
						. $matches[2][$i];
				}
			}
		}
		// try to give back a SQL anyway
		return "ALTER TABLE $quotedTable CHANGE "
			. $this->connection->quoteColumnName($oldName) . ' '
			. $this->connection->quoteColumnName($newName);
	}

	/**
	 * Builds a SQL statement for dropping a foreign key constraint.
	 * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping a foreign key constraint.
	 */
	public function dropForeignKey($name, $table)
	{
		return 'ALTER TABLE ' . $this->connection->quoteTableName($table)
			. ' DROP FOREIGN KEY ' . $this->connection->quoteColumnName($name);
	}
}
