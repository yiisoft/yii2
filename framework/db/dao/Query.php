<?php
/**
 * Query class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\dao;

/**
 * Query represents a SQL statement in a way that is independent of DBMS.
 *
 * Query not only can represent a SELECT statement, it can also represent INSERT, UPDATE, DELETE,
 * and other commonly used DDL statements, such as CREATE TABLE, CREATE INDEX, etc.
 *
 * Query provides a set of methods to facilitate the specification of different clauses.
 * These methods can be chained together. For example,
 *
 * ~~~
 * $query = new Query;
 * $query->select('id, name')
 *     ->from('tbl_user')
 *     ->limit(10);
 * // get the actual SQL statement
 * echo $query->getSql();
 * // or execute the query
 * $users = $query->createCommand()->queryAll();
 * ~~~
 *
 * By calling [[getSql()]], we can obtain the actual SQL statement from a Query object.
 * And by calling [[createCommand()]], we can get a [[Command]] instance which can be further
 * used to perform/execute the DB query against a database.
 *
 * @property string $sql the SQL statement represented by this query object.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Query extends BaseQuery
{
	/**
	 * @var array the operation that this query represents. This refers to the method call as well as
	 * the corresponding parameters for constructing a non-select SQL statement (e.g. INSERT, CREATE TABLE).
	 * This property is mainly maintained by methods such as [[insert()]], [[update()]], [[createTable()]].
	 * If this property is not set, it means this query represents a SELECT statement.
	 */
	public $operation;


	/**
	 * Generates and returns the SQL statement according to this query.
	 * @param Connection $connection the database connection used to generate the SQL statement.
	 * If this parameter is not given, the `db` application component will be used.
	 * @return string the generated SQL statement
	 */
	public function getSql($connection = null)
	{
		if ($connection === null) {
			$connection = \Yii::$application->db;
		}
		return $connection->getQueryBuilder()->build($this);
	}

	/**
	 * Creates a DB command that can be used to execute this query.
	 * @param Connection $connection the database connection used to generate the SQL statement.
	 * If this parameter is not given, the `db` application component will be used.
	 * @return Command the created DB command instance.
	 */
	public function createCommand($connection = null)
	{
		if ($connection === null) {
			$connection = \Yii::$application->db;
		}
		return $connection->createCommand($this);
	}

	/**
	 * Creates and executes an INSERT SQL statement.
	 * The method will properly escape the column names, and bind the values to be inserted.
	 * @param string $table the table that new rows will be inserted into.
	 * @param array $columns the column data (name=>value) to be inserted into the table.
	 * @return Query the query object itself
	 */
	public function insert($table, $columns)
	{
		$this->operation = array(__FUNCTION__, $table, $columns, array());
		return $this;
	}

	/**
	 * Creates and executes an UPDATE SQL statement.
	 * The method will properly escape the column names and bind the values to be updated.
	 * @param string $table the table to be updated.
	 * @param array $columns the column data (name=>value) to be updated.
	 * @param string|array $condition the conditions that will be put in the WHERE part.
	 * Please refer to [[where()]] on how to specify this parameter.
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * Please refer to [[where()]] on alternative syntax of specifying anonymous parameters.
	 * @return Query the query object itself
	 */
	public function update($table, $columns, $condition = '', $params = array())
	{
		if (!is_array($params)) {
			$params = func_get_args();
			array_shift($params);
			array_shift($params);
			unset($params[0]);
		}
		$this->addParams($params);
		$this->operation = array(__FUNCTION__, $table, $columns, $condition, array());
		return $this;
	}

	/**
	 * Creates and executes a DELETE SQL statement.
	 * @param string $table the table where the data will be deleted from.
	 * @param string|array $condition the conditions that will be put in the WHERE part.
	 * Please refer to [[where()]] on how to specify this parameter.
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * Please refer to [[where()]] on alternative syntax of specifying anonymous parameters.
	 * @return Query the query object itself
	 */
	public function delete($table, $condition = '', $params = array())
	{
		if (!is_array($params)) {
			$params = func_get_args();
			array_shift($params);
			unset($params[0]);
		}
		$this->operation = array(__FUNCTION__, $table, $condition);
		return $this->addParams($params);
	}

	/**
	 * Builds and executes a SQL statement for creating a new DB table.
	 *
	 * The columns in the new  table should be specified as name-definition pairs (e.g. 'name'=>'string'),
	 * where name stands for a column name which will be properly quoted by the method, and definition
	 * stands for the column type which can contain an abstract DB type.
	 * The method [[\yii\db\dao\QueryBuilder::getColumnType()]] will be called
	 * to convert the abstract column types to physical ones. For example, `string` will be converted
	 * as `varchar(255)`, and `string not null` becomes `varchar(255) not null`.
	 *
	 * If a column is specified with definition only (e.g. 'PRIMARY KEY (name, type)'), it will be directly
	 * inserted into the generated SQL.
	 *
	 * @param string $table the name of the table to be created. The name will be properly quoted by the method.
	 * @param array $columns the columns (name=>definition) in the new table.
	 * @param string $options additional SQL fragment that will be appended to the generated SQL.
	 * @return Query the query object itself
	 */
	public function createTable($table, $columns, $options = null)
	{
		$this->operation = array(__FUNCTION__, $table, $columns, $options);
		return $this;
	}

	/**
	 * Builds and executes a SQL statement for renaming a DB table.
	 * @param string $table the table to be renamed. The name will be properly quoted by the method.
	 * @param string $newName the new table name. The name will be properly quoted by the method.
	 * @return Query the query object itself
	 */
	public function renameTable($table, $newName)
	{
		$this->operation = array(__FUNCTION__, $table, $newName);
		return $this;
	}

	/**
	 * Builds and executes a SQL statement for dropping a DB table.
	 * @param string $table the table to be dropped. The name will be properly quoted by the method.
	 * @return Query the query object itself
	 */
	public function dropTable($table)
	{
		$this->operation = array(__FUNCTION__, $table);
		return $this;
	}

	/**
	 * Builds and executes a SQL statement for truncating a DB table.
	 * @param string $table the table to be truncated. The name will be properly quoted by the method.
	 * @return Query the query object itself
	 */
	public function truncateTable($table)
	{
		$this->operation = array(__FUNCTION__, $table);
		return $this;
	}

	/**
	 * Builds and executes a SQL statement for adding a new DB column.
	 * @param string $table the table that the new column will be added to. The table name will be properly quoted by the method.
	 * @param string $column the name of the new column. The name will be properly quoted by the method.
	 * @param string $type the column type. [[\yii\db\dao\QueryBuilder::getColumnType()]] will be called
	 * to convert the give column type to the physical one. For example, `string` will be converted
	 * as `varchar(255)`, and `string not null` becomes `varchar(255) not null`.
	 * @return Query the query object itself
	 */
	public function addColumn($table, $column, $type)
	{
		$this->operation = array(__FUNCTION__, $table, $column, $type);
		return $this;
	}

	/**
	 * Builds and executes a SQL statement for dropping a DB column.
	 * @param string $table the table whose column is to be dropped. The name will be properly quoted by the method.
	 * @param string $column the name of the column to be dropped. The name will be properly quoted by the method.
	 * @return Query the query object itself
	 */
	public function dropColumn($table, $column)
	{
		$this->operation = array(__FUNCTION__, $table, $column);
		return $this;
	}

	/**
	 * Builds and executes a SQL statement for renaming a column.
	 * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
	 * @param string $oldName the old name of the column. The name will be properly quoted by the method.
	 * @param string $newName the new name of the column. The name will be properly quoted by the method.
	 * @return Query the query object itself
	 */
	public function renameColumn($table, $oldName, $newName)
	{
		$this->operation = array(__FUNCTION__, $table, $oldName, $newName);
		return $this;
	}

	/**
	 * Builds and executes a SQL statement for changing the definition of a column.
	 * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
	 * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
	 * @param string $type the column type. [[\yii\db\dao\QueryBuilder::getColumnType()]] will be called
	 * to convert the give column type to the physical one. For example, `string` will be converted
	 * as `varchar(255)`, and `string not null` becomes `varchar(255) not null`.
	 * @return Query the query object itself
	 */
	public function alterColumn($table, $column, $type)
	{
		$this->operation = array(__FUNCTION__, $table, $column, $type);
		return $this;
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
	 * @return Query the query object itself
	 */
	public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
	{
		$this->operation = array(__FUNCTION__, $name, $table, $columns, $refTable, $refColumns, $delete, $update);
		return $this;
	}

	/**
	 * Builds a SQL statement for dropping a foreign key constraint.
	 * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
	 * @return Query the query object itself
	 */
	public function dropForeignKey($name, $table)
	{
		$this->operation = array(__FUNCTION__, $name, $table);
		return $this;
	}

	/**
	 * Builds and executes a SQL statement for creating a new index.
	 * @param string $name the name of the index. The name will be properly quoted by the method.
	 * @param string $table the table that the new index will be created for. The table name will be properly quoted by the method.
	 * @param string $columns the column(s) that should be included in the index. If there are multiple columns, please separate them
	 * by commas. The column names will be properly quoted by the method.
	 * @param boolean $unique whether to add UNIQUE constraint on the created index.
	 * @return Query the query object itself
	 */
	public function createIndex($name, $table, $columns, $unique = false)
	{
		$this->operation = array(__FUNCTION__, $name, $table, $columns, $unique);
		return $this;
	}

	/**
	 * Builds and executes a SQL statement for dropping an index.
	 * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
	 * @return Query the query object itself
	 */
	public function dropIndex($name, $table)
	{
		$this->operation = array(__FUNCTION__, $name, $table);
		return $this;
	}
}
