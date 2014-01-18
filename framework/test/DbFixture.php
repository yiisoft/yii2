<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Connection;

/**
 * DbFixture is the base class for DB-related fixtures.
 *
 * DbFixture provides the [[db]] connection as well as a set of commonly used DB manipulation methods.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class DbFixture extends Fixture
{
	/**
	 * @var Connection|string the DB connection object or the application component ID of the DB connection.
	 * After the DbFixture object is created, if you want to change this property, you should only assign it
	 * with a DB connection object.
	 */
	public $db = 'db';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if (is_string($this->db)) {
			$this->db = Yii::$app->getComponent($this->db);
		}
		if (!is_object($this->db)) {
			throw new InvalidConfigException("The 'db' property must be either a DB connection instance or the application component ID of a DB connection.");
		}
	}

	/**
	 * Executes a SQL statement.
	 * This method executes the specified SQL statement using [[db]].
	 * @param string $sql the SQL statement to be executed
	 * @param array $params input parameters (name => value) for the SQL execution.
	 * See [[Command::execute()]] for more details.
	 */
	public function execute($sql, $params = [])
	{
		$this->db->createCommand($sql)->execute($params);
	}

	/**
	 * Creates and executes an INSERT SQL statement.
	 * The method will properly escape the column names, and bind the values to be inserted.
	 * @param string $table the table that new rows will be inserted into.
	 * @param array $columns the column data (name => value) to be inserted into the table.
	 */
	public function insert($table, $columns)
	{
		$this->db->createCommand()->insert($table, $columns)->execute();
	}

	/**
	 * Creates and executes an batch INSERT SQL statement.
	 * The method will properly escape the column names, and bind the values to be inserted.
	 * @param string $table the table that new rows will be inserted into.
	 * @param array $columns the column names.
	 * @param array $rows the rows to be batch inserted into the table
	 */
	public function batchInsert($table, $columns, $rows)
	{
		$this->db->createCommand()->batchInsert($table, $columns, $rows)->execute();
	}

	/**
	 * Creates and executes an UPDATE SQL statement.
	 * The method will properly escape the column names and bind the values to be updated.
	 * @param string $table the table to be updated.
	 * @param array $columns the column data (name => value) to be updated.
	 * @param array|string $condition the conditions that will be put in the WHERE part. Please
	 * refer to [[Query::where()]] on how to specify conditions.
	 * @param array $params the parameters to be bound to the query.
	 */
	public function update($table, $columns, $condition = '', $params = [])
	{
		$this->db->createCommand()->update($table, $columns, $condition, $params)->execute();
	}

	/**
	 * Creates and executes a DELETE SQL statement.
	 * @param string $table the table where the data will be deleted from.
	 * @param array|string $condition the conditions that will be put in the WHERE part. Please
	 * refer to [[Query::where()]] on how to specify conditions.
	 * @param array $params the parameters to be bound to the query.
	 */
	public function delete($table, $condition = '', $params = [])
	{
		$this->db->createCommand()->delete($table, $condition, $params)->execute();
	}

	/**
	 * Builds and executes a SQL statement for creating a new DB table.
	 *
	 * The columns in the new  table should be specified as name-definition pairs (e.g. 'name' => 'string'),
	 * where name stands for a column name which will be properly quoted by the method, and definition
	 * stands for the column type which can contain an abstract DB type.
	 *
	 * The [[QueryBuilder::getColumnType()]] method will be invoked to convert any abstract type into a physical one.
	 *
	 * If a column is specified with definition only (e.g. 'PRIMARY KEY (name, type)'), it will be directly
	 * put into the generated SQL.
	 *
	 * @param string $table the name of the table to be created. The name will be properly quoted by the method.
	 * @param array $columns the columns (name => definition) in the new table.
	 * @param string $options additional SQL fragment that will be appended to the generated SQL.
	 */
	public function createTable($table, $columns, $options = null)
	{
		$this->db->createCommand()->createTable($table, $columns, $options)->execute();
	}

	/**
	 * Builds and executes a SQL statement for renaming a DB table.
	 * @param string $table the table to be renamed. The name will be properly quoted by the method.
	 * @param string $newName the new table name. The name will be properly quoted by the method.
	 */
	public function renameTable($table, $newName)
	{
		$this->db->createCommand()->renameTable($table, $newName)->execute();
	}

	/**
	 * Builds and executes a SQL statement for dropping a DB table.
	 * @param string $table the table to be dropped. The name will be properly quoted by the method.
	 */
	public function dropTable($table)
	{
		$this->db->createCommand()->dropTable($table)->execute();
	}

	/**
	 * Builds and executes a SQL statement for truncating a DB table.
	 * @param string $table the table to be truncated. The name will be properly quoted by the method.
	 */
	public function truncateTable($table)
	{
		$this->db->createCommand()->truncateTable($table)->execute();
	}

	/**
	 * Builds and executes a SQL statement for adding a new DB column.
	 * @param string $table the table that the new column will be added to. The table name will be properly quoted by the method.
	 * @param string $column the name of the new column. The name will be properly quoted by the method.
	 * @param string $type the column type. The [[QueryBuilder::getColumnType()]] method will be invoked to convert abstract column type (if any)
	 * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
	 * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
	 */
	public function addColumn($table, $column, $type)
	{
		$this->db->createCommand()->addColumn($table, $column, $type)->execute();
	}

	/**
	 * Builds and executes a SQL statement for dropping a DB column.
	 * @param string $table the table whose column is to be dropped. The name will be properly quoted by the method.
	 * @param string $column the name of the column to be dropped. The name will be properly quoted by the method.
	 */
	public function dropColumn($table, $column)
	{
		$this->db->createCommand()->dropColumn($table, $column)->execute();
	}

	/**
	 * Builds and executes a SQL statement for renaming a column.
	 * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
	 * @param string $name the old name of the column. The name will be properly quoted by the method.
	 * @param string $newName the new name of the column. The name will be properly quoted by the method.
	 */
	public function renameColumn($table, $name, $newName)
	{
		$this->db->createCommand()->renameColumn($table, $name, $newName)->execute();
	}

	/**
	 * Builds and executes a SQL statement for changing the definition of a column.
	 * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
	 * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
	 * @param string $type the new column type. The [[getColumnType()]] method will be invoked to convert abstract column type (if any)
	 * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
	 * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
	 */
	public function alterColumn($table, $column, $type)
	{
		$this->db->createCommand()->alterColumn($table, $column, $type)->execute();
	}

	/**
	 * Builds and executes a SQL statement for creating a primary key.
	 * The method will properly quote the table and column names.
	 * @param string $name the name of the primary key constraint.
	 * @param string $table the table that the primary key constraint will be added to.
	 * @param string|array $columns comma separated string or array of columns that the primary key will consist of.
	 */
	public function addPrimaryKey($name, $table, $columns)
	{
		$this->db->createCommand()->addPrimaryKey($name, $table, $columns)->execute();
	}

	/**
	 * Builds and executes a SQL statement for dropping a primary key.
	 * @param string $name the name of the primary key constraint to be removed.
	 * @param string $table the table that the primary key constraint will be removed from.
	 */
	public function dropPrimaryKey($name, $table)
	{
		$this->db->createCommand()->dropPrimaryKey($name, $table)->execute();
	}

	/**
	 * Builds a SQL statement for adding a foreign key constraint to an existing table.
	 * The method will properly quote the table and column names.
	 * @param string $name the name of the foreign key constraint.
	 * @param string $table the table that the foreign key constraint will be added to.
	 * @param string $columns the name of the column to that the constraint will be added on. If there are multiple columns, separate them with commas.
	 * @param string $refTable the table that the foreign key references to.
	 * @param string $refColumns the name of the column that the foreign key references to. If there are multiple columns, separate them with commas.
	 * @param string $delete the ON DELETE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
	 * @param string $update the ON UPDATE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
	 */
	public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
	{
		$this->db->createCommand()->addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update)->execute();
	}

	/**
	 * Builds a SQL statement for dropping a foreign key constraint.
	 * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
	 */
	public function dropForeignKey($name, $table)
	{
		$this->db->createCommand()->dropForeignKey($name, $table)->execute();
	}

	/**
	 * Builds and executes a SQL statement for creating a new index.
	 * @param string $name the name of the index. The name will be properly quoted by the method.
	 * @param string $table the table that the new index will be created for. The table name will be properly quoted by the method.
	 * @param string $column the column(s) that should be included in the index. If there are multiple columns, please separate them
	 * by commas. The column names will be properly quoted by the method.
	 * @param boolean $unique whether to add UNIQUE constraint on the created index.
	 */
	public function createIndex($name, $table, $column, $unique = false)
	{
		$this->db->createCommand()->createIndex($name, $table, $column, $unique)->execute();
	}

	/**
	 * Builds and executes a SQL statement for dropping an index.
	 * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
	 */
	public function dropIndex($name, $table)
	{
		$this->db->createCommand()->dropIndex($name, $table)->execute();
	}
	/**
	 * Creates and executes a SQL command to reset the sequence value of a table's primary key.
	 * The sequence will be reset such that the primary key of the next new row inserted
	 * will have the specified value or 1.
	 * @param string $table the name of the table whose primary key sequence will be reset
	 * @param mixed $value the value for the primary key of the next new row inserted. If this is not set,
	 * the next new row's primary key will have a value 1.
	 */
	public function resetSequence($table, $value = null)
	{
		$this->db->createCommand()->resetSequence($table, $value)->execute();
	}

	/**
	 * Builds and executes a SQL command for enabling or disabling integrity check.
	 * @param boolean $check whether to turn on or off the integrity check.
	 * @param string $schema the schema name of the tables. Defaults to empty string, meaning the current
	 * or default schema.
	 * @param string $table the table name.
	 */
	public function checkIntegrity($check = true, $schema = '', $table = '')
	{
		$this->db->createCommand()->checkIntegrity($check, $schema, $table)->execute();
	}
}
