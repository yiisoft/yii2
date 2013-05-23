<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use Yii;
use yii\base\NotSupportedException;
use yii\caching\Cache;

/**
 * Command represents a SQL statement to be executed against a database.
 *
 * A command object is usually created by calling [[Connection::createCommand()]].
 * The SQL statement it represents can be set via the [[sql]] property.
 *
 * To execute a non-query SQL (such as INSERT, DELETE, UPDATE), call [[execute()]].
 * To execute a SQL statement that returns result data set (such as SELECT),
 * use [[queryAll()]], [[queryRow()]], [[queryColumn()]], [[queryScalar()]], or [[query()]].
 * For example,
 *
 * ~~~
 * $users = $connection->createCommand('SELECT * FROM tbl_user')->queryAll();
 * ~~~
 *
 * Command supports SQL statement preparation and parameter binding.
 * Call [[bindValue()]] to bind a value to a SQL parameter;
 * Call [[bindParam()]] to bind a PHP variable to a SQL parameter.
 * When binding a parameter, the SQL statement is automatically prepared.
 * You may also call [[prepare()]] explicitly to prepare a SQL statement.
 *
 * Command also supports building SQL statements by providing methods such as [[insert()]],
 * [[update()]], etc. For example,
 *
 * ~~~
 * $connection->createCommand()->insert('tbl_user', array(
 *     'name' => 'Sam',
 *     'age' => 30,
 * ))->execute();
 * ~~~
 *
 * To build SELECT SQL statements, please use [[QueryBuilder]] instead.
 *
 * @property string $sql the SQL statement to be executed
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
	 * @var \PDOStatement the PDOStatement object that this command is associated with
	 */
	public $pdoStatement;
	/**
	 * @var mixed the default fetch mode for this command.
	 * @see http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php
	 */
	public $fetchMode = \PDO::FETCH_ASSOC;
	/**
	 * @var string the SQL statement that this command represents
	 */
	private $_sql;
	/**
	 * @var array the parameter log information (name => value)
	 */
	private $_params = array();

	/**
	 * Returns the SQL statement for this command.
	 * @return string the SQL statement to be executed
	 */
	public function getSql()
	{
		return $this->_sql;
	}

	/**
	 * Specifies the SQL statement to be executed.
	 * The previous SQL execution (if any) will be cancelled, and [[params]] will be cleared as well.
	 * @param string $sql the SQL statement to be set.
	 * @return Command this command instance
	 */
	public function setSql($sql)
	{
		if ($sql !== $this->_sql) {
			$this->cancel();
			$this->_sql = $this->db->quoteSql($sql);
			$this->_params = array();
		}
		return $this;
	}

	/**
	 * Returns the raw SQL by inserting parameter values into the corresponding placeholders in [[sql]].
	 * Note that the return value of this method should mainly be used for logging purpose.
	 * It is likely that this method returns an invalid SQL due to improper replacement of parameter placeholders.
	 * @return string the raw SQL
	 */
	public function getRawSql()
	{
		if (empty($this->_params)) {
			return $this->_sql;
		} else {
			$params = array();
			foreach ($this->_params as $name => $value) {
				if (is_string($value)) {
					$params[$name] = $this->db->quoteValue($value);
				} elseif ($value === null) {
					$params[$name] = 'NULL';
				} else {
					$params[$name] = $value;
				}
			}
			if (isset($params[1])) {
				$sql = '';
				foreach (explode('?', $this->_sql) as $i => $part) {
					$sql .= (isset($params[$i]) ? $params[$i] : '') . $part;
				}
				return $sql;
			} else {
				return strtr($this->_sql, $params);
			}
		}
	}

	/**
	 * Prepares the SQL statement to be executed.
	 * For complex SQL statement that is to be executed multiple times,
	 * this may improve performance.
	 * For SQL statement with binding parameters, this method is invoked
	 * automatically.
	 * @throws Exception if there is any DB error
	 */
	public function prepare()
	{
		if ($this->pdoStatement == null) {
			$sql = $this->getSql();
			try {
				$this->pdoStatement = $this->db->pdo->prepare($sql);
			} catch (\Exception $e) {
				Yii::error($e->getMessage() . "\nFailed to prepare SQL: $sql", __METHOD__);
				$errorInfo = $e instanceof \PDOException ? $e->errorInfo : null;
				throw new Exception($e->getMessage(), $errorInfo, (int)$e->getCode());
			}
		}
	}

	/**
	 * Cancels the execution of the SQL statement.
	 * This method mainly sets [[pdoStatement]] to be null.
	 */
	public function cancel()
	{
		$this->pdoStatement = null;
	}

	/**
	 * Binds a parameter to the SQL statement to be executed.
	 * @param string|integer $name parameter identifier. For a prepared statement
	 * using named placeholders, this will be a parameter name of
	 * the form `:name`. For a prepared statement using question mark
	 * placeholders, this will be the 1-indexed position of the parameter.
	 * @param mixed $value Name of the PHP variable to bind to the SQL statement parameter
	 * @param integer $dataType SQL data type of the parameter. If null, the type is determined by the PHP type of the value.
	 * @param integer $length length of the data type
	 * @param mixed $driverOptions the driver-specific options
	 * @return Command the current command being executed
	 * @see http://www.php.net/manual/en/function.PDOStatement-bindParam.php
	 */
	public function bindParam($name, &$value, $dataType = null, $length = null, $driverOptions = null)
	{
		$this->prepare();
		if ($dataType === null) {
			$this->pdoStatement->bindParam($name, $value, $this->getPdoType($value));
		} elseif ($length === null) {
			$this->pdoStatement->bindParam($name, $value, $dataType);
		} elseif ($driverOptions === null) {
			$this->pdoStatement->bindParam($name, $value, $dataType, $length);
		} else {
			$this->pdoStatement->bindParam($name, $value, $dataType, $length, $driverOptions);
		}
		$this->_params[$name] =& $value;
		return $this;
	}

	/**
	 * Binds a value to a parameter.
	 * @param string|integer $name Parameter identifier. For a prepared statement
	 * using named placeholders, this will be a parameter name of
	 * the form `:name`. For a prepared statement using question mark
	 * placeholders, this will be the 1-indexed position of the parameter.
	 * @param mixed $value The value to bind to the parameter
	 * @param integer $dataType SQL data type of the parameter. If null, the type is determined by the PHP type of the value.
	 * @return Command the current command being executed
	 * @see http://www.php.net/manual/en/function.PDOStatement-bindValue.php
	 */
	public function bindValue($name, $value, $dataType = null)
	{
		$this->prepare();
		if ($dataType === null) {
			$this->pdoStatement->bindValue($name, $value, $this->getPdoType($value));
		} else {
			$this->pdoStatement->bindValue($name, $value, $dataType);
		}
		$this->_params[$name] = $value;
		return $this;
	}

	/**
	 * Binds a list of values to the corresponding parameters.
	 * This is similar to [[bindValue()]] except that it binds multiple values at a time.
	 * Note that the SQL data type of each value is determined by its PHP type.
	 * @param array $values the values to be bound. This must be given in terms of an associative
	 * array with array keys being the parameter names, and array values the corresponding parameter values,
	 * e.g. `array(':name' => 'John', ':age' => 25)`. By default, the PDO type of each value is determined
	 * by its PHP type. You may explicitly specify the PDO type by using an array: `array(value, type)`,
	 * e.g. `array(':name' => 'John', ':profile' => array($profile, \PDO::PARAM_LOB))`.
	 * @return Command the current command being executed
	 */
	public function bindValues($values)
	{
		if (!empty($values)) {
			$this->prepare();
			foreach ($values as $name => $value) {
				if (is_array($value)) {
					$type = $value[1];
					$value = $value[0];
				} else {
					$type = $this->getPdoType($value);
				}
				$this->pdoStatement->bindValue($name, $value, $type);
				$this->_params[$name] = $value;
			}
		}
		return $this;
	}

	/**
	 * Determines the PDO type for the give PHP data value.
	 * @param mixed $data the data whose PDO type is to be determined
	 * @return integer the PDO type
	 * @see http://www.php.net/manual/en/pdo.constants.php
	 */
	private function getPdoType($data)
	{
		static $typeMap = array(
			'boolean' => \PDO::PARAM_BOOL,
			'integer' => \PDO::PARAM_INT,
			'string' => \PDO::PARAM_STR,
			'resource' => \PDO::PARAM_LOB,
			'NULL' => \PDO::PARAM_NULL,
		);
		$type = gettype($data);
		return isset($typeMap[$type]) ? $typeMap[$type] : \PDO::PARAM_STR;
	}

	/**
	 * Executes the SQL statement.
	 * This method should only be used for executing non-query SQL statement, such as `INSERT`, `DELETE`, `UPDATE` SQLs.
	 * No result set will be returned.
	 * @return integer number of rows affected by the execution.
	 * @throws Exception execution failed
	 */
	public function execute()
	{
		$sql = $this->getSql();

		$rawSql = $this->getRawSql();

		Yii::trace("Executing SQL: $rawSql", __METHOD__);

		if ($sql == '') {
			return 0;
		}

		try {
			$token = "SQL: $sql";
			Yii::beginProfile($token, __METHOD__);

			$this->prepare();
			$this->pdoStatement->execute();
			$n = $this->pdoStatement->rowCount();

			Yii::endProfile($token, __METHOD__);
			return $n;
		} catch (\Exception $e) {
			Yii::endProfile($token, __METHOD__);
			$message = $e->getMessage();

			Yii::error("$message\nFailed to execute SQL: $rawSql", __METHOD__);

			$errorInfo = $e instanceof \PDOException ? $e->errorInfo : null;
			throw new Exception($message, $errorInfo, (int)$e->getCode());
		}
	}

	/**
	 * Executes the SQL statement and returns query result.
	 * This method is for executing a SQL query that returns result set, such as `SELECT`.
	 * @return DataReader the reader object for fetching the query result
	 * @throws Exception execution failed
	 */
	public function query()
	{
		return $this->queryInternal('');
	}

	/**
	 * Executes the SQL statement and returns ALL rows at once.
	 * @param mixed $fetchMode the result fetch mode. Please refer to [PHP manual](http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
	 * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
	 * @return array all rows of the query result. Each array element is an array representing a row of data.
	 * An empty array is returned if the query results in nothing.
	 * @throws Exception execution failed
	 */
	public function queryAll($fetchMode = null)
	{
		return $this->queryInternal('fetchAll', $fetchMode);
	}

	/**
	 * Executes the SQL statement and returns the first row of the result.
	 * This method is best used when only the first row of result is needed for a query.
	 * @param mixed $fetchMode the result fetch mode. Please refer to [PHP manual](http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
	 * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
	 * @return array|boolean the first row (in terms of an array) of the query result. False is returned if the query
	 * results in nothing.
	 * @throws Exception execution failed
	 */
	public function queryRow($fetchMode = null)
	{
		return $this->queryInternal('fetch', $fetchMode);
	}

	/**
	 * Executes the SQL statement and returns the value of the first column in the first row of data.
	 * This method is best used when only a single value is needed for a query.
	 * @return string|boolean the value of the first column in the first row of the query result.
	 * False is returned if there is no value.
	 * @throws Exception execution failed
	 */
	public function queryScalar()
	{
		$result = $this->queryInternal('fetchColumn', 0);
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
	 * @return array the first column of the query result. Empty array is returned if the query results in nothing.
	 * @throws Exception execution failed
	 */
	public function queryColumn()
	{
		return $this->queryInternal('fetchAll', \PDO::FETCH_COLUMN);
	}

	/**
	 * Performs the actual DB query of a SQL statement.
	 * @param string $method method of PDOStatement to be called
	 * @param mixed $fetchMode the result fetch mode. Please refer to [PHP manual](http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
	 * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
	 * @return mixed the method execution result
	 * @throws Exception if the query causes any problem
	 */
	private function queryInternal($method, $fetchMode = null)
	{
		$db = $this->db;
		$sql = $this->getSql();
		$rawSql = $this->getRawSql();

		Yii::trace("Querying SQL: $rawSql", __METHOD__);

		/** @var $cache \yii\caching\Cache */
		if ($db->enableQueryCache && $method !== '') {
			$cache = is_string($db->queryCache) ? Yii::$app->getComponent($db->queryCache) : $db->queryCache;
		}

		if (isset($cache) && $cache instanceof Cache) {
			$cacheKey = $cache->buildKey(array(
				__CLASS__,
				$db->dsn,
				$db->username,
				$rawSql,
			));
			if (($result = $cache->get($cacheKey)) !== false) {
				Yii::trace('Query result served from cache', __METHOD__);
				return $result;
			}
		}

		try {
			$token = "SQL: $sql";
			Yii::beginProfile($token, __METHOD__);

			$this->prepare();
			$this->pdoStatement->execute();

			if ($method === '') {
				$result = new DataReader($this);
			} else {
				if ($fetchMode === null) {
					$fetchMode = $this->fetchMode;
				}
				$result = call_user_func_array(array($this->pdoStatement, $method), (array)$fetchMode);
				$this->pdoStatement->closeCursor();
			}

			Yii::endProfile($token, __METHOD__);

			if (isset($cache, $cacheKey) && $cache instanceof Cache) {
				$cache->set($cacheKey, $result, $db->queryCacheDuration, $db->queryCacheDependency);
				Yii::trace('Saved query result in cache', __METHOD__);
			}

			return $result;
		} catch (\Exception $e) {
			Yii::endProfile($token, __METHOD__);
			$message = $e->getMessage();
			Yii::error("$message\nCommand::$method() failed: $rawSql", __METHOD__);
			$errorInfo = $e instanceof \PDOException ? $e->errorInfo : null;
			throw new Exception($message, $errorInfo, (int)$e->getCode());
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
	 * @param array $columns the column data (name => value) to be inserted into the table.
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
	 * @param array $columns the column data (name => value) to be updated.
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
		$sql = $this->db->getQueryBuilder()->delete($table, $condition, $params);
		return $this->setSql($sql)->bindValues($params);
	}


	/**
	 * Creates a SQL command for creating a new DB table.
	 *
	 * The columns in the new table should be specified as name-definition pairs (e.g. 'name' => 'string'),
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
	 * @param array $columns the columns (name => definition) in the new table.
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
