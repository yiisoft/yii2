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
    public $columnTypes = array(
        'pk' => 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
        'string' => 'varchar(255)',
        'text' => 'text',
		'smallint' => 'smallint',
        'integer' => 'int(11)',
		'bigint'=> 'bigint',
        'boolean' => 'tinyint(1)',
        'float' => 'float',
        'decimal' => 'decimal',
		'money' => 'decimal(19,4)',
        'datetime' => 'datetime',
        'timestamp' => 'timestamp',
        'time' => 'time',
        'date' => 'date',
        'binary' => 'blob',
    );

	/**
	 * Builds a SQL statement for renaming a column.
	 * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
	 * @param string $name the old name of the column. The name will be properly quoted by the method.
	 * @param string $newName the new name of the column. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB column.
	 * @since 1.1.6
	 */
	public function renameColumn($table, $name, $newName)
	{
		$db = $this->getDbConnection();
		$row = $db->createCommand('SHOW CREATE TABLE ' . $db->quoteTableName($table))->queryRow();
		if ($row === false)
			throw new CDbException(Yii::t('yii', 'Unable to find "{column}" in table "{table}".', array('{column}' => $name, '{table}' => $table)));
		if (isset($row['Create Table']))
			$sql = $row['Create Table'];
		else
		{
			$row = array_values($row);
			$sql = $row[1];
		}
		if (preg_match_all('/^\s*`(.*?)`\s+(.*?),?$/m', $sql, $matches))
		{
			foreach ($matches[1] as $i => $c)
			{
				if ($c === $name)
				{
					return "ALTER TABLE " . $db->quoteTableName($table)
						. " CHANGE " . $db->quoteColumnName($name)
						. ' ' . $db->quoteColumnName($newName) . ' ' . $matches[2][$i];
				}
			}
		}

		// try to give back a SQL anyway
		return "ALTER TABLE " . $db->quoteTableName($table)
			. " CHANGE " . $db->quoteColumnName($name) . ' ' . $newName;
	}

	/**
	 * Builds a SQL statement for dropping a foreign key constraint.
	 * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping a foreign key constraint.
	 * @since 1.1.6
	 */
	public function dropForeignKey($name, $table)
	{
		return 'ALTER TABLE ' . $this->quoteTableName($table)
			. ' DROP FOREIGN KEY ' . $this->quoteColumnName($name);
	}
}
