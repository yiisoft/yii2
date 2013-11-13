<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

use Yii;
use yii\base\Component;
use yii\caching\Cache;
use yii\db\Exception;

/**
 * Class Command
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Command extends Component
{
	/**
	 * @var Connection the Sphinx connection that this command is associated with
	 */
	public $db;
	/**
	 * @var \PDOStatement the PDOStatement object that this command is associated with
	 */
	public $pdoStatement;
	/**
	 * @var integer the default fetch mode for this command.
	 * @see http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php
	 */
	public $fetchMode = \PDO::FETCH_ASSOC;
	/**
	 * @var array the parameters (name => value) that are bound to the current PDO statement.
	 * This property is maintained by methods such as [[bindValue()]].
	 * Do not modify it directly.
	 */
	public $params = [];
	/**
	 * @var string the SphinxQL statement that this command represents
	 */
	private $_sql;

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
	 * @return static this command instance
	 */
	public function setSql($sql)
	{
		if ($sql !== $this->_sql) {
			$this->cancel();
			$this->_sql = $this->db->quoteSql($sql);
			$this->params = [];
		}
		return $this;
	}

	/**
	 * Returns the raw SQL by inserting parameter values into the corresponding placeholders in [[sql]].
	 * Note that the return value of this method should mainly be used for logging purpose.
	 * It is likely that this method returns an invalid SQL due to improper replacement of parameter placeholders.
	 * @return string the raw SQL with parameter values inserted into the corresponding placeholders in [[sql]].
	 */
	public function getRawSql()
	{
		if (empty($this->params)) {
			return $this->_sql;
		} else {
			$params = [];
			foreach ($this->params as $name => $value) {
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
				$message = $e->getMessage() . "\nFailed to prepare SQL: $sql";
				$errorInfo = $e instanceof \PDOException ? $e->errorInfo : null;
				throw new Exception($message, $errorInfo, (int)$e->getCode(), $e);
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
	 * @return static the current command being executed
	 * @see http://www.php.net/manual/en/function.PDOStatement-bindParam.php
	 */
	public function bindParam($name, &$value, $dataType = null, $length = null, $driverOptions = null)
	{
		$this->prepare();
		if ($dataType === null) {
			$dataType = $this->db->getSchema()->getPdoType($value);
		}
		if ($length === null) {
			$this->pdoStatement->bindParam($name, $value, $dataType);
		} elseif ($driverOptions === null) {
			$this->pdoStatement->bindParam($name, $value, $dataType, $length);
		} else {
			$this->pdoStatement->bindParam($name, $value, $dataType, $length, $driverOptions);
		}
		$this->params[$name] =& $value;
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
	 * @return static the current command being executed
	 * @see http://www.php.net/manual/en/function.PDOStatement-bindValue.php
	 */
	public function bindValue($name, $value, $dataType = null)
	{
		$this->prepare();
		if ($dataType === null) {
			$dataType = $this->db->getSchema()->getPdoType($value);
		}
		$this->pdoStatement->bindValue($name, $value, $dataType);
		$this->params[$name] = $value;
		return $this;
	}

	/**
	 * Binds a list of values to the corresponding parameters.
	 * This is similar to [[bindValue()]] except that it binds multiple values at a time.
	 * Note that the SQL data type of each value is determined by its PHP type.
	 * @param array $values the values to be bound. This must be given in terms of an associative
	 * array with array keys being the parameter names, and array values the corresponding parameter values,
	 * e.g. `[':name' => 'John', ':age' => 25]`. By default, the PDO type of each value is determined
	 * by its PHP type. You may explicitly specify the PDO type by using an array: `[value, type]`,
	 * e.g. `[':name' => 'John', ':profile' => [$profile, \PDO::PARAM_LOB]]`.
	 * @return static the current command being executed
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
					$type = $this->db->getSchema()->getPdoType($value);
				}
				$this->pdoStatement->bindValue($name, $value, $type);
				$this->params[$name] = $value;
			}
		}
		return $this;
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

		Yii::trace($rawSql, __METHOD__);

		if ($sql == '') {
			return 0;
		}

		$token = $rawSql;
		try {
			Yii::beginProfile($token, __METHOD__);

			$this->prepare();
			$this->pdoStatement->execute();
			$n = $this->pdoStatement->rowCount();

			Yii::endProfile($token, __METHOD__);
			return $n;
		} catch (\Exception $e) {
			Yii::endProfile($token, __METHOD__);
			$message = $e->getMessage() . "\nThe SQL being executed was: $rawSql";
			$errorInfo = $e instanceof \PDOException ? $e->errorInfo : null;
			throw new Exception($message, $errorInfo, (int)$e->getCode(), $e);
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
	 * @param integer $fetchMode the result fetch mode. Please refer to [PHP manual](http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
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
	 * @param integer $fetchMode the result fetch mode. Please refer to [PHP manual](http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
	 * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
	 * @return array|boolean the first row (in terms of an array) of the query result. False is returned if the query
	 * results in nothing.
	 * @throws Exception execution failed
	 */
	public function queryOne($fetchMode = null)
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
	 * @param integer $fetchMode the result fetch mode. Please refer to [PHP manual](http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
	 * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
	 * @return mixed the method execution result
	 * @throws Exception if the query causes any problem
	 */
	private function queryInternal($method, $fetchMode = null)
	{
		$db = $this->db;
		$rawSql = $this->getRawSql();

		Yii::trace($rawSql, __METHOD__);

		/** @var $cache \yii\caching\Cache */
		if ($db->enableQueryCache && $method !== '') {
			$cache = is_string($db->queryCache) ? Yii::$app->getComponent($db->queryCache) : $db->queryCache;
		}

		if (isset($cache) && $cache instanceof Cache) {
			$cacheKey = [
				__CLASS__,
				$db->dsn,
				$db->username,
				$rawSql,
			];
			if (($result = $cache->get($cacheKey)) !== false) {
				Yii::trace('Query result served from cache', __METHOD__);
				return $result;
			}
		}

		$token = $rawSql;
		try {
			Yii::beginProfile($token, __METHOD__);

			$this->prepare();
			$this->pdoStatement->execute();

			if ($method === '') {
				$result = new DataReader($this);
			} else {
				if ($fetchMode === null) {
					$fetchMode = $this->fetchMode;
				}
				$result = call_user_func_array([$this->pdoStatement, $method], (array)$fetchMode);
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
			$message = $e->getMessage()  . "\nThe SQL being executed was: $rawSql";
			$errorInfo = $e instanceof \PDOException ? $e->errorInfo : null;
			throw new Exception($message, $errorInfo, (int)$e->getCode(), $e);
		}
	}

	/**
	 * Creates an INSERT command.
	 * For example,
	 *
	 * ~~~
	 * $connection->createCommand()->insert('idx_user', [
	 *     'name' => 'Sam',
	 *     'age' => 30,
	 * ])->execute();
	 * ~~~
	 *
	 * The method will properly escape the column names, and bind the values to be inserted.
	 *
	 * Note that the created command is not executed until [[execute()]] is called.
	 *
	 * @param string $index the index that new rows will be inserted into.
	 * @param array $columns the column data (name => value) to be inserted into the index.
	 * @return static the command object itself
	 */
	public function insert($index, $columns)
	{
		$params = [];
		$sql = $this->db->getQueryBuilder()->insert($index, $columns, $params);
		return $this->setSql($sql)->bindValues($params);
	}

	/**
	 * Creates a batch INSERT command.
	 * For example,
	 *
	 * ~~~
	 * $connection->createCommand()->batchInsert('idx_user', ['name', 'age'], [
	 *     ['Tom', 30],
	 *     ['Jane', 20],
	 *     ['Linda', 25],
	 * ])->execute();
	 * ~~~
	 *
	 * Note that the values in each row must match the corresponding column names.
	 *
	 * @param string $index the index that new rows will be inserted into.
	 * @param array $columns the column names
	 * @param array $rows the rows to be batch inserted into the index
	 * @return static the command object itself
	 */
	public function batchInsert($index, $columns, $rows)
	{
		$params = [];
		$sql = $this->db->getQueryBuilder()->batchInsert($index, $columns, $rows, $params);
		return $this->setSql($sql)->bindValues($params);
	}

	/**
	 * Creates an UPDATE command.
	 * For example,
	 *
	 * ~~~
	 * $connection->createCommand()->update('tbl_user', ['status' => 1], 'age > 30')->execute();
	 * ~~~
	 *
	 * The method will properly escape the column names and bind the values to be updated.
	 *
	 * Note that the created command is not executed until [[execute()]] is called.
	 *
	 * @param string $index the index to be updated.
	 * @param array $columns the column data (name => value) to be updated.
	 * @param string|array $condition the condition that will be put in the WHERE part. Please
	 * refer to [[Query::where()]] on how to specify condition.
	 * @param array $params the parameters to be bound to the command
	 * @return static the command object itself
	 */
	public function update($index, $columns, $condition = '', $params = [])
	{
		$sql = $this->db->getQueryBuilder()->update($index, $columns, $condition, $params);
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
	 * The method will properly escape the index and column names.
	 *
	 * Note that the created command is not executed until [[execute()]] is called.
	 *
	 * @param string $index the index where the data will be deleted from.
	 * @param string|array $condition the condition that will be put in the WHERE part. Please
	 * refer to [[Query::where()]] on how to specify condition.
	 * @param array $params the parameters to be bound to the command
	 * @return static the command object itself
	 */
	public function delete($index, $condition = '', $params = [])
	{
		$sql = $this->db->getQueryBuilder()->delete($index, $condition, $params);
		return $this->setSql($sql)->bindValues($params);
	}

	/**
	 * Creates a SQL command for truncating a runtime index.
	 * @param string $index the index to be truncated. The name will be properly quoted by the method.
	 * @return static the command object itself
	 */
	public function truncateIndex($index)
	{
		$sql = $this->db->getQueryBuilder()->truncateIndex($index);
		return $this->setSql($sql);
	}

	/**
	 * Builds a snippet from provided data and query, using specified index settings.
	 * @param string $index name of the index, from which to take the text processing settings.
	 * @param string|array $source is the source data to extract a snippet from.
	 * It could be either a single string or array of strings.
	 * @param string $query the full-text query to build snippets for.
	 * @param array $options list of options in format: optionName => optionValue
	 * @return static the command object itself
	 */
	public function callSnippets($index, $source, $query, $options = [])
	{
		$params = [];
		$sql = $this->db->getQueryBuilder()->callSnippets($index, $source, $query, $options, $params);
		return $this->setSql($sql)->bindValues($params);
	}

	/**
	 * Returns tokenized and normalized forms of the keywords, and, optionally, keyword statistics.
	 * @param string $index the name of the index from which to take the text processing settings
	 * @param string $text the text to break down to keywords.
	 * @param boolean $fetchStatistic whether to return document and hit occurrence statistics
	 * @return string the SQL statement for call keywords.
	 */
	public function callKeywords($index, $text, $fetchStatistic = false)
	{
		$params = [];
		$sql = $this->db->getQueryBuilder()->callKeywords($index, $text, $fetchStatistic, $params);
		return $this->setSql($sql)->bindValues($params);
	}
}