<?php
/**
 * Command class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\redis;

use yii\base\NotSupportedException;
use yii\db\Exception;

// TODO ensure proper value quoting against "SQL"injection

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Command extends \yii\base\Component
{
	/**
	 * @var Connection the DB connection that this command is associated with
	 */
	public $db;
	/**
	 * @var array the parameter log information (name=>value)
	 */
	private $_params = array();

	private $_query;


	/**
	 * Determines the PDO type for the give PHP data value.
	 * @param mixed $data the data whose PDO type is to be determined
	 * @return integer the PDO type
	 * @see http://www.php.net/manual/en/pdo.constants.php
	 */
	private function getRedisType($data)
	{
		static $typeMap = array(
			'boolean' => \PDO::PARAM_BOOL,
			'integer' => \PDO::PARAM_INT,
			'string' => \PDO::PARAM_STR,
			'NULL' => \PDO::PARAM_NULL,
		);
		$type = gettype($data);
		return isset($typeMap[$type]) ? $typeMap[$type] : \PDO::PARAM_STR;
	}

	/**
	 * Executes the SQL statement.
	 * This method should only be used for executing non-query SQL statement, such as `INSERT`, `DELETE`, `UPDATE` SQLs.
	 * No result set will be returned.
	 * @param array $params input parameters (name=>value) for the SQL execution. This is an alternative
	 * to [[bindValues()]]. Note that if you pass parameters in this way, any previous call to [[bindParam()]]
	 * or [[bindValue()]] will be ignored.
	 * @return integer number of rows affected by the execution.
	 * @throws Exception execution failed
	 */
	public function execute()
	{
		$query = $this->_query;

		if ($this->_params === array()) {
			$paramLog = '';
		} else {
			$paramLog = "\nParameters: " . var_export($this->_params, true);
		}

		\Yii::trace("Executing SQL: {$query}{$paramLog}", __CLASS__);

		if ($query == '') {
			return 0;
		}

		try {
			if ($this->db->enableProfiling) {
				\Yii::beginProfile(__METHOD__ . "($query)", __CLASS__);
			}

			$n = $this->db->redis->send_command(array_merge(array($query), $this->_params));

			if ($this->db->enableProfiling) {
				\Yii::endProfile(__METHOD__ . "($query)", __CLASS__);
			}
			return $n;
		} catch (\Exception $e) {
			if ($this->db->enableProfiling) {
				\Yii::endProfile(__METHOD__ . "($query)", __CLASS__);
			}
			$message = $e->getMessage();

			\Yii::error("$message\nFailed to execute SQL: {$query}{$paramLog}", __CLASS__);

			throw new Exception($message, (int)$e->getCode());
		}
	}

	/**
	 * Executes the SQL statement and returns query result.
	 * This method is for executing a SQL query that returns result set, such as `SELECT`.
	 * @param array $params input parameters (name=>value) for the SQL execution. This is an alternative
	 * to [[bindValues()]]. Note that if you pass parameters in this way, any previous call to [[bindParam()]]
	 * or [[bindValue()]] will be ignored.
	 * @return DataReader the reader object for fetching the query result
	 * @throws Exception execution failed
	 */
	public function query($params = array())
	{
		return $this->queryInternal('', $params);
	}

	/**
	 * Executes the SQL statement and returns ALL rows at once.
	 * @param array $params input parameters (name=>value) for the SQL execution. This is an alternative
	 * to [[bindValues()]]. Note that if you pass parameters in this way, any previous call to [[bindParam()]]
	 * or [[bindValue()]] will be ignored.
	 * @param mixed $fetchMode the result fetch mode. Please refer to [PHP manual](http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
	 * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
	 * @return array all rows of the query result. Each array element is an array representing a row of data.
	 * An empty array is returned if the query results in nothing.
	 * @throws Exception execution failed
	 */
	public function queryAll($params = array(), $fetchMode = null)
	{
		return $this->queryInternal('fetchAll', $params, $fetchMode);
	}

	/**
	 * Executes the SQL statement and returns the first row of the result.
	 * This method is best used when only the first row of result is needed for a query.
	 * @param array $params input parameters (name=>value) for the SQL execution. This is an alternative
	 * to [[bindValues()]]. Note that if you pass parameters in this way, any previous call to [[bindParam()]]
	 * or [[bindValue()]] will be ignored.
	 * @param mixed $fetchMode the result fetch mode. Please refer to [PHP manual](http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
	 * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
	 * @return array|boolean the first row (in terms of an array) of the query result. False is returned if the query
	 * results in nothing.
	 * @throws Exception execution failed
	 */
	public function queryRow($params = array(), $fetchMode = null)
	{
		return $this->queryInternal('fetch', $params, $fetchMode);
	}

	/**
	 * Executes the SQL statement and returns the value of the first column in the first row of data.
	 * This method is best used when only a single value is needed for a query.
	 * @param array $params input parameters (name=>value) for the SQL execution. This is an alternative
	 * to [[bindValues()]]. Note that if you pass parameters in this way, any previous call to [[bindParam()]]
	 * or [[bindValue()]] will be ignored.
	 * @return string|boolean the value of the first column in the first row of the query result.
	 * False is returned if there is no value.
	 * @throws Exception execution failed
	 */
	public function queryScalar($params = array())
	{
		$result = $this->queryInternal('fetchColumn', $params, 0);
		if (is_resource($result) && get_resource_type($result) === 'stream') {
			return stream_get_contents($result);
		} else {
			return $result;
		}
	}

	/**
	 * Executes the SQL statement and returns the first column of the result.
	 * This method is best used when only the first column of result (i.e. the first element in each row)
	 * is needed for a query.
	 * @param array $params input parameters (name=>value) for the SQL execution. This is an alternative
	 * to [[bindValues()]]. Note that if you pass parameters in this way, any previous call to [[bindParam()]]
	 * or [[bindValue()]] will be ignored.
	 * @return array the first column of the query result. Empty array is returned if the query results in nothing.
	 * @throws Exception execution failed
	 */
	public function queryColumn($params = array())
	{
		return $this->queryInternal('fetchAll', $params, \PDO::FETCH_COLUMN);
	}

	/**
	 * Performs the actual DB query of a SQL statement.
	 * @param string $method method of PDOStatement to be called
	 * @param array $params input parameters (name=>value) for the SQL execution. This is an alternative
	 * to [[bindValues()]]. Note that if you pass parameters in this way, any previous call to [[bindParam()]]
	 * or [[bindValue()]] will be ignored.
	 * @param mixed $fetchMode the result fetch mode. Please refer to [PHP manual](http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
	 * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
	 * @return mixed the method execution result
	 * @throws Exception if the query causes any problem
	 */
	private function queryInternal($method, $params, $fetchMode = null)
	{
		$db = $this->db;
		$sql = $this->getSql();
		$this->_params = array_merge($this->_params, $params);
		if ($this->_params === array()) {
			$paramLog = '';
		} else {
			$paramLog = "\nParameters: " . var_export($this->_params, true);
		}

		\Yii::trace("Querying SQL: {$sql}{$paramLog}", __CLASS__);

		/** @var $cache \yii\caching\Cache */
		if ($db->enableQueryCache && $method !== '') {
			$cache = \Yii::$application->getComponent($db->queryCacheID);
		}

		if (isset($cache)) {
			$cacheKey = $cache->buildKey(__CLASS__, $db->dsn, $db->username, $sql, $paramLog);
			if (($result = $cache->get($cacheKey)) !== false) {
				\Yii::trace('Query result found in cache', __CLASS__);
				return $result;
			}
		}

		try {
			if ($db->enableProfiling) {
				\Yii::beginProfile(__METHOD__ . "($sql)", __CLASS__);
			}

			$this->prepare();
			if ($params === array()) {
				$this->pdoStatement->execute();
			} else {
				$this->pdoStatement->execute($params);
			}

			if ($method === '') {
				$result = new DataReader($this);
			} else {
				if ($fetchMode === null) {
					$fetchMode = $this->fetchMode;
				}
				$result = call_user_func_array(array($this->pdoStatement, $method), (array)$fetchMode);
				$this->pdoStatement->closeCursor();
			}

			if ($db->enableProfiling) {
				\Yii::endProfile(__METHOD__ . "($sql)", __CLASS__);
			}

			if (isset($cache, $cacheKey)) {
				$cache->set($cacheKey, $result, $db->queryCacheDuration, $db->queryCacheDependency);
				\Yii::trace('Saved query result in cache', __CLASS__);
			}

			return $result;
		} catch (\Exception $e) {
			if ($db->enableProfiling) {
				\Yii::endProfile(__METHOD__ . "($sql)", __CLASS__);
			}
			$message = $e->getMessage();
			\Yii::error("$message\nCommand::$method() failed: {$sql}{$paramLog}", __CLASS__);
			$errorInfo = $e instanceof \PDOException ? $e->errorInfo : null;
			throw new Exception($message, (int)$e->getCode(), $errorInfo);
		}
	}

	/**
	 * Creates an INSERT command.
	 * For example,
	 *
	 * ~~~
	 * $connection->createCommand()->insert('tbl_user', array(
	 *     'name' => 'Sam',
	 *     'age' => 30,
	 * ))->execute();
	 * ~~~
	 *
	 * The method will properly escape the column names, and bind the values to be inserted.
	 *
	 * Note that the created command is not executed until [[execute()]] is called.
	 *
	 * @param string $table the table that new rows will be inserted into.
	 * @param array $columns the column data (name=>value) to be inserted into the table.
	 * @return Command the command object itself
	 */
	public function insert($table, $columns)
	{
		$params = array();
		$sql = $this->db->getQueryBuilder()->insert($table, $columns, $params);
		return $this->setSql($sql)->bindValues($params);
	}

	/**
	 * Creates a batch INSERT command.
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
	 * @return Command the command object itself
	 */
	public function batchInsert($table, $columns, $rows)
	{
		$sql = $this->db->getQueryBuilder()->batchInsert($table, $columns, $rows);
		return $this->setSql($sql);
	}

	/**
	 * Creates an UPDATE command.
	 * For example,
	 *
	 * ~~~
	 * $connection->createCommand()->update('tbl_user', array(
	 *     'status' => 1,
	 * ), 'age > 30')->execute();
	 * ~~~
	 *
	 * The method will properly escape the column names and bind the values to be updated.
	 *
	 * Note that the created command is not executed until [[execute()]] is called.
	 *
	 * @param string $table the table to be updated.
	 * @param array $columns the column data (name=>value) to be updated.
	 * @param mixed $condition the condition that will be put in the WHERE part. Please
	 * refer to [[Query::where()]] on how to specify condition.
	 * @param array $params the parameters to be bound to the command
	 * @return Command the command object itself
	 */
	public function update($table, $columns, $condition = '', $params = array())
	{
		$sql = $this->db->getQueryBuilder()->update($table, $columns, $condition, $params);
		return $this->setSql($sql)->bindValues($params);
	}

	/**
	 * Creates a DELETE command.
	 * For example,
	 *
	 * ~~~
	 * $connection->createCommand()->delete('tbl_user', 'status = 0')->execute();
	 * ~~~
	 *
	 * The method will properly escape the table and column names.
	 *
	 * Note that the created command is not executed until [[execute()]] is called.
	 *
	 * @param string $table the table where the data will be deleted from.
	 * @param mixed $condition the condition that will be put in the WHERE part. Please
	 * refer to [[Query::where()]] on how to specify condition.
	 * @param array $params the parameters to be bound to the command
	 * @return Command the command object itself
	 */
	public function delete($table, $condition = '', $params = array())
	{
		$sql = $this->db->getQueryBuilder()->delete($table, $condition);
		return $this->setSql($sql)->bindValues($params);
	}


	/**
	 * Creates a SQL command for creating a new DB table.
	 *
	 * The columns in the new table should be specified as name-definition pairs (e.g. 'name'=>'string'),
	 * where name stands for a column name which will be properly quoted by the method, and definition
	 * stands for the column type which can contain an abstract DB type.
	 * The method [[QueryBuilder::getColumnType()]] will be called
	 * to convert the abstract column types to physical ones. For example, `string` will be converted
	 * as `varchar(255)`, and `string not null` becomes `varchar(255) not null`.
	 *
	 * If a column is specified with definition only (e.g. 'PRIMARY KEY (name, type)'), it will be directly
	 * inserted into the generated SQL.
	 *
	 * @param string $table the name of the table to be created. The name will be properly quoted by the method.
	 * @param array $columns the columns (name=>definition) in the new table.
	 * @param string $options additional SQL fragment that will be appended to the generated SQL.
	 * @return Command the command object itself
	 */
	public function createTable($table, $columns, $options = null)
	{
		$sql = $this->db->getQueryBuilder()->createTable($table, $columns, $options);
		return $this->setSql($sql);
	}

	/**
	 * Creates a SQL command for renaming a DB table.
	 * @param string $table the table to be renamed. The name will be properly quoted by the method.
	 * @param string $newName the new table name. The name will be properly quoted by the method.
	 * @return Command the command object itself
	 */
	public function renameTable($table, $newName)
	{
		$sql = $this->db->getQueryBuilder()->renameTable($table, $newName);
		return $this->setSql($sql);
	}

	/**
	 * Creates a SQL command for dropping a DB table.
	 * @param string $table the table to be dropped. The name will be properly quoted by the method.
	 * @return Command the command object itself
	 */
	public function dropTable($table)
	{
		$sql = $this->db->getQueryBuilder()->dropTable($table);
		return $this->setSql($sql);
	}

	/**
	 * Creates a SQL command for truncating a DB table.
	 * @param string $table the table to be truncated. The name will be properly quoted by the method.
	 * @return Command the command object itself
	 */
	public function truncateTable($table)
	{
		$sql = $this->db->getQueryBuilder()->truncateTable($table);
		return $this->setSql($sql);
	}

	/**
	 * Creates a SQL command for adding a new DB column.
	 * @param string $table the table that the new column will be added to. The table name will be properly quoted by the method.
	 * @param string $column the name of the new column. The name will be properly quoted by the method.
	 * @param string $type the column type. [[\yii\db\QueryBuilder::getColumnType()]] will be called
	 * to convert the give column type to the physical one. For example, `string` will be converted
	 * as `varchar(255)`, and `string not null` becomes `varchar(255) not null`.
	 * @return Command the command object itself
	 */
	public function addColumn($table, $column, $type)
	{
		$sql = $this->db->getQueryBuilder()->addColumn($table, $column, $type);
		return $this->setSql($sql);
	}

	/**
	 * Creates a SQL command for dropping a DB column.
	 * @param string $table the table whose column is to be dropped. The name will be properly quoted by the method.
	 * @param string $column the name of the column to be dropped. The name will be properly quoted by the method.
	 * @return Command the command object itself
	 */
	public function dropColumn($table, $column)
	{
		$sql = $this->db->getQueryBuilder()->dropColumn($table, $column);
		return $this->setSql($sql);
	}

	/**
	 * Creates a SQL command for renaming a column.
	 * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
	 * @param string $oldName the old name of the column. The name will be properly quoted by the method.
	 * @param string $newName the new name of the column. The name will be properly quoted by the method.
	 * @return Command the command object itself
	 */
	public function renameColumn($table, $oldName, $newName)
	{
		$sql = $this->db->getQueryBuilder()->renameColumn($table, $oldName, $newName);
		return $this->setSql($sql);
	}

	/**
	 * Creates a SQL command for changing the definition of a column.
	 * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
	 * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
	 * @param string $type the column type. [[\yii\db\QueryBuilder::getColumnType()]] will be called
	 * to convert the give column type to the physical one. For example, `string` will be converted
	 * as `varchar(255)`, and `string not null` becomes `varchar(255) not null`.
	 * @return Command the command object itself
	 */
	public function alterColumn($table, $column, $type)
	{
		$sql = $this->db->getQueryBuilder()->alterColumn($table, $column, $type);
		return $this->setSql($sql);
	}

	/**
	 * Creates a SQL command for adding a foreign key constraint to an existing table.
	 * The method will properly quote the table and column names.
	 * @param string $name the name of the foreign key constraint.
	 * @param string $table the table that the foreign key constraint will be added to.
	 * @param string $columns the name of the column to that the constraint will be added on. If there are multiple columns, separate them with commas.
	 * @param string $refTable the table that the foreign key references to.
	 * @param string $refColumns the name of the column that the foreign key references to. If there are multiple columns, separate them with commas.
	 * @param string $delete the ON DELETE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
	 * @param string $update the ON UPDATE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
	 * @return Command the command object itself
	 */
	public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
	{
		$sql = $this->db->getQueryBuilder()->addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update);
		return $this->setSql($sql);
	}

	/**
	 * Creates a SQL command for dropping a foreign key constraint.
	 * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
	 * @return Command the command object itself
	 */
	public function dropForeignKey($name, $table)
	{
		$sql = $this->db->getQueryBuilder()->dropForeignKey($name, $table);
		return $this->setSql($sql);
	}

	/**
	 * Creates a SQL command for creating a new index.
	 * @param string $name the name of the index. The name will be properly quoted by the method.
	 * @param string $table the table that the new index will be created for. The table name will be properly quoted by the method.
	 * @param string $columns the column(s) that should be included in the index. If there are multiple columns, please separate them
	 * by commas. The column names will be properly quoted by the method.
	 * @param boolean $unique whether to add UNIQUE constraint on the created index.
	 * @return Command the command object itself
	 */
	public function createIndex($name, $table, $columns, $unique = false)
	{
		$sql = $this->db->getQueryBuilder()->createIndex($name, $table, $columns, $unique);
		return $this->setSql($sql);
	}

	/**
	 * Creates a SQL command for dropping an index.
	 * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
	 * @return Command the command object itself
	 */
	public function dropIndex($name, $table)
	{
		$sql = $this->db->getQueryBuilder()->dropIndex($name, $table);
		return $this->setSql($sql);
	}

	/**
	 * Creates a SQL command for resetting the sequence value of a table's primary key.
	 * The sequence will be reset such that the primary key of the next new row inserted
	 * will have the specified value or 1.
	 * @param string $table the name of the table whose primary key sequence will be reset
	 * @param mixed $value the value for the primary key of the next new row inserted. If this is not set,
	 * the next new row's primary key will have a value 1.
	 * @return Command the command object itself
	 * @throws NotSupportedException if this is not supported by the underlying DBMS
	 */
	public function resetSequence($table, $value = null)
	{
		$sql = $this->db->getQueryBuilder()->resetSequence($table, $value);
		return $this->setSql($sql);
	}

	/**
	 * Builds a SQL command for enabling or disabling integrity check.
	 * @param boolean $check whether to turn on or off the integrity check.
	 * @param string $schema the schema name of the tables. Defaults to empty string, meaning the current
	 * or default schema.
	 * @return Command the command object itself
	 * @throws NotSupportedException if this is not supported by the underlying DBMS
	 */
	public function checkIntegrity($check = true, $schema = '')
	{
		$sql = $this->db->getQueryBuilder()->checkIntegrity($check, $schema);
		return $this->setSql($sql);
	}
}
