<?php
/**
 * QueryBuilder class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\sqlite;

use yii\db\Exception;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;

/**
 * QueryBuilder is the query builder for SQLite databases.
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
		Schema::TYPE_PK => 'integer PRIMARY KEY AUTOINCREMENT NOT NULL',
		Schema::TYPE_STRING => 'varchar(255)',
		Schema::TYPE_TEXT => 'text',
		Schema::TYPE_SMALLINT => 'smallint',
		Schema::TYPE_INTEGER => 'integer',
		Schema::TYPE_BIGINT => 'bigint',
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
		$db = $this->db;
		$table = $db->getTableSchema($tableName);
		if ($table !== null && $table->sequenceName !== null) {
			if ($value === null) {
				$key = reset($table->primaryKey);
				$tableName = $db->quoteTableName($tableName);
				$value = $db->createCommand("SELECT MAX('$key') FROM $tableName")->queryScalar();
			} else {
				$value = (int)$value - 1;
			}
			try {
				// it's possible sqlite_sequence does not exist
				$db->createCommand("UPDATE sqlite_sequence SET seq='$value' WHERE name='{$table->name}'")->execute();
			} catch (Exception $e) {
			}
		} elseif ($table === null) {
			throw new InvalidParamException("Table not found: $tableName");
		} else {
			throw new InvalidParamException("There is not sequence associated with table '$tableName'.'");
		}
	}

	/**
	 * Enables or disables integrity check.
	 * @param boolean $check whether to turn on or off the integrity check.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * @throws NotSupportedException this is not supported by SQLite
	 */
	public function checkIntegrity($check = true, $schema = '')
	{
		throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
	}

	/**
	 * Builds a SQL statement for truncating a DB table.
	 * @param string $table the table to be truncated. The name will be properly quoted by the method.
	 * @return string the SQL statement for truncating a DB table.
	 */
	public function truncateTable($table)
	{
		return "DELETE FROM " . $this->db->quoteTableName($table);
	}

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
	 * Builds a SQL statement for dropping a DB column.
	 * @param string $table the table whose column is to be dropped. The name will be properly quoted by the method.
	 * @param string $column the name of the column to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping a DB column.
	 * @throws NotSupportedException this is not supported by SQLite
	 */
	public function dropColumn($table, $column)
	{
		throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
	}

	/**
	 * Builds a SQL statement for renaming a column.
	 * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
	 * @param string $oldName the old name of the column. The name will be properly quoted by the method.
	 * @param string $newName the new name of the column. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB column.
	 * @throws NotSupportedException this is not supported by SQLite
	 */
	public function renameColumn($table, $oldName, $newName)
	{
		throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
	}

	/**
	 * Builds a SQL statement for adding a foreign key constraint to an existing table.
	 * The method will properly quote the table and column names.
	 * @param string $name the name of the foreign key constraint.
	 * @param string $table the table that the foreign key constraint will be added to.
	 * @param string|array $columns the name of the column to that the constraint will be added on.
	 * If there are multiple columns, separate them with commas or use an array to represent them.
	 * @param string $refTable the table that the foreign key references to.
	 * @param string|array $refColumns the name of the column that the foreign key references to.
	 * If there are multiple columns, separate them with commas or use an array to represent them.
	 * @param string $delete the ON DELETE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
	 * @param string $update the ON UPDATE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
	 * @return string the SQL statement for adding a foreign key constraint to an existing table.
	 * @throws NotSupportedException this is not supported by SQLite
	 */
	public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
	{
		throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
	}

	/**
	 * Builds a SQL statement for dropping a foreign key constraint.
	 * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping a foreign key constraint.
	 * @throws NotSupportedException this is not supported by SQLite
	 */
	public function dropForeignKey($name, $table)
	{
		throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
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
	 * @throws NotSupportedException this is not supported by SQLite
	 */
	public function alterColumn($table, $column, $type)
	{
		throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
	}
}
