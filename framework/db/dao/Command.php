<?php
/**
 * Command class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\dao;

use yii\db\Exception;

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
 * $users = \Yii::$application->db->createCommand('SELECT * FROM tbl_user')->queryAll();
 * ~~~
 *
 * Command supports SQL statement preparation and parameter binding.
 * Call [[bindValue()]] to bind a value to a SQL parameter;
 * Call [[bindParam()]] to bind a PHP variable to a SQL parameter.
 * When binding a parameter, the SQL statement is automatically prepared.
 * You may also call [[prepare()]] explicitly to prepare a SQL statement.
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
	public $connection;
	/**
	 * @var \PDOStatement the PDOStatement object that this command contains
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
	 * @var array the parameter log information (name=>value)
	 */
	private $_params = array();

	/**
	 * Constructor.
	 * @param Connection $connection the database connection
	 * @param string $sql the SQL statement to be executed
	 * @param array $params the parameters to be bound to the SQL statement
	 */
	public function __construct($connection, $sql = null, $params = array())
	{
		$this->connection = $connection;
		$this->_sql = $sql;
		$this->bindValues($params);
	}

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
	 * Any previous execution will be terminated or cancelled.
	 * @param string $value the SQL statement to be set.
	 * @return Command this command instance
	 */
	public function setSql($value)
	{
		$this->_sql = $value;
		$this->_params = array();
		$this->cancel();
		return $this;
	}

	/**
	 * Prepares the SQL statement to be executed.
	 * For complex SQL statement that is to be executed multiple times,
	 * this may improve performance.
	 * For SQL statement with binding parameters, this method is invoked
	 * automatically.
	 */
	public function prepare()
	{
		if ($this->pdoStatement == null) {
			$sql = $this->connection->expandTablePrefix($this->getSql());
			try {
				$this->pdoStatement = $this->connection->pdo->prepare($sql);
			} catch (\Exception $e) {
				\Yii::error($e->getMessage() . "\nFailed to prepare SQL: $sql", __CLASS__);
				$errorInfo = $e instanceof \PDOException ? $e->errorInfo : null;
				throw new Exception($e->getMessage(), (int)$e->getCode(), $errorInfo);
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
			$this->pdoStatement->bindParam($name, $value, $this->connection->getPdoType(gettype($value)));
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
			$this->pdoStatement->bindValue($name, $value, $this->connection->getPdoType(gettype($value)));
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
	 * e.g. `array(':name'=>'John', ':age'=>25)`.
	 * @return Command the current command being executed
	 */
	public function bindValues($values)
	{
		if (!empty($values)) {
			$this->prepare();
			foreach ($values as $name => $value) {
				$this->pdoStatement->bindValue($name, $value, $this->connection->getPdoType(gettype($value)));
				$this->_params[$name] = $value;
			}
		}
		return $this;
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
	public function execute($params = array())
	{
		$sql = $this->connection->expandTablePrefix($this->getSql());
		$this->_params = array_merge($this->_params, $params);
		if ($this->_params === array()) {
			$paramLog = '';
		} else {
			$paramLog = "\nParameters: " . var_export($this->_params, true);
		}

		\Yii::trace("Executing SQL: {$sql}{$paramLog}", __CLASS__);
echo $sql . "\n\n";
		try {
			if ($this->connection->enableProfiling) {
				\Yii::beginProfile(__METHOD__ . "($sql)", __CLASS__);
			}

			$this->prepare();
			if ($params === array()) {
				$this->pdoStatement->execute();
			} else {
				$this->pdoStatement->execute($params);
			}
			$n = $this->pdoStatement->rowCount();

			if ($this->connection->enableProfiling) {
				\Yii::endProfile(__METHOD__ . "($sql)", __CLASS__);
			}
			return $n;
		} catch (\Exception $e) {
			if ($this->connection->enableProfiling) {
				\Yii::endProfile(__METHOD__ . "($sql)", __CLASS__);
			}
			$message = $e->getMessage();
			\Yii::error("$message\nFailed to execute SQL: {$sql}{$paramLog}", __CLASS__);
			$errorInfo = $e instanceof \PDOException ? $e->errorInfo : null;
			throw new Exception($message, (int)$e->getCode(), $errorInfo);
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
	 */
	private function queryInternal($method, $params, $fetchMode = null)
	{
		$db = $this->connection;
		$sql = $db->expandTablePrefix($this->getSql());
		$this->_params = array_merge($this->_params, $params);
		if ($this->_params === array()) {
			$paramLog = '';
		} else {
			$paramLog = "\nParameters: " . var_export($this->_params, true);
		}

		\Yii::trace("Querying SQL: {$sql}{$paramLog}", __CLASS__);
echo $sql . "\n\n";
		if ($db->queryCachingCount > 0 && $db->queryCachingDuration >= 0 && $method !== '') {
			$cache = \Yii::$application->getComponent($db->queryCacheID);
		}

		if (isset($cache)) {
			$db->queryCachingCount--;
			$cacheKey = __CLASS__ . "/{$db->dsn}/{$db->username}/$sql/$paramLog";
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

			if (isset($cache)) {
				$cache->set($cacheKey, $result, $db->queryCachingDuration, $db->queryCachingDependency);
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
}
