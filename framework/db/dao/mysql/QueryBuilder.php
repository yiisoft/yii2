<?php
/**
 * QueryBuilder class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\dao\mysql;

/**
 * QueryBuilder builds a SQL statement based on the specification given as a [[Query]] object.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\db\dao\QueryBuilder
{
	/**
	 * @var array the abstract column types mapped to physical column types.
	 */
    public $typeMap = array(
		'pk' => 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
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
	 * @param string $name the old name of the column. The name will be properly quoted by the method.
	 * @param string $newName the new name of the column. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB column.
	 */
	public function renameColumn($table, $name, $newName)
	{
		$quotedTable = $this->driver->quoteTableName($table);
		$row = $this->connection->createCommand('SHOW CREATE TABLE ' . $quotedTable)->queryRow();
		if ($row === false)
			throw new CDbException(Yii::t('yii', 'Unable to find "{column}" in table "{table}".', array('{column}' => $name, '{table}' => $table)));
		if (isset($row['Create Table'])) {
			$sql = $row['Create Table'];
		} else {
			$row = array_values($row);
			$sql = $row[1];
		}
		if (preg_match_all('/^\s*`(.*?)`\s+(.*?),?$/m', $sql, $matches)) {
			foreach ($matches[1] as $i => $c) {
				if ($c === $name) {
					return "ALTER TABLE $quotedTable CHANGE " . $this->driver->quoteColumnName($name)
						. ' ' . $this->driver->quoteColumnName($newName) . ' ' . $matches[2][$i];
				}
			}
		}
		// try to give back a SQL anyway
		return "ALTER TABLE $quotedTable CHANGE " . $this->driver->quoteColumnName($name) . ' ' . $newName;
	}

	/**
	 * Builds a SQL statement for dropping a foreign key constraint.
	 * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping a foreign key constraint.
	 */
	public function dropForeignKey($name, $table)
	{
		return 'ALTER TABLE ' . $this->driver->quoteTableName($table)
			. ' DROP FOREIGN KEY ' . $this->driver->quoteColumnName($name);
	}
}
