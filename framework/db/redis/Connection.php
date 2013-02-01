<?php
/**
 * Connection class file
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\redis;

use \yii\base\Component;
use yii\base\NotSupportedException;
use yii\base\InvalidConfigException;
use \yii\db\Exception;

/**
 *
 *
 *
 *
 * @since 2.0
 */
class Connection extends Component
{
	/**
	 * @event Event an event that is triggered after a DB connection is established
	 */
	const EVENT_AFTER_OPEN = 'afterOpen';

	/**
	 * @var string the Data Source Name, or DSN, contains the information required to connect to the database.
	 * DSN format: redis://[auth@][server][:port][/db]
	 * @see charset
	 */
	public $dsn;
	/**
	 * @var string the username for establishing DB connection. Defaults to empty string.
	 */
	public $username = '';
	/**
	 * @var string the password for establishing DB connection. Defaults to empty string.
	 */
	public $password = '';

	/**
	 * @var \Jamm\Memory\RedisServer
	 */
	public $redis;

	/**
	 * @var boolean whether to enable profiling for the SQL statements being executed.
	 * Defaults to false. This should be mainly enabled and used during development
	 * to find out the bottleneck of SQL executions.
	 * @see getStats
	 */
	public $enableProfiling = false;
	/**
	 * @var string the common prefix or suffix for table names. If a table name is given
	 * as `{{%TableName}}`, then the percentage character `%` will be replaced with this
	 * property value. For example, `{{%post}}` becomes `{{tbl_post}}` if this property is
	 * set as `"tbl_"`. Note that this property is only effective when [[enableAutoQuoting]]
	 * is true.
	 * @see enableAutoQuoting
	 */
	public $keyPrefix;
	/**
	 * @var Transaction the currently active transaction
	 */
	private $_transaction;

	/**
	 * Closes the connection when this component is being serialized.
	 * @return array
	 */
	public function __sleep()
	{
		$this->close();
		return array_keys(get_object_vars($this));
	}

	/**
	 * Returns a value indicating whether the DB connection is established.
	 * @return boolean whether the DB connection is established
	 */
	public function getIsActive()
	{
		return $this->redis !== null;
	}

	/**
	 * Establishes a DB connection.
	 * It does nothing if a DB connection has already been established.
	 * @throws Exception if connection fails
	 */
	public function open()
	{
		if ($this->redis === null) {
			if (empty($this->dsn)) {
				throw new InvalidConfigException('Connection.dsn cannot be empty.');
			}
			// TODO parse DSN
			$host = 'localhost';
			$port = 6379;
			try {
				\Yii::trace('Opening DB connection: ' . $this->dsn, __CLASS__);
				// TODO connection to redis seems to be very easy, consider writing own connect
				$this->redis = new \Jamm\Memory\RedisServer($host, $port);
				$this->initConnection();
			}
			catch (\PDOException $e) {
				\Yii::error("Failed to open DB connection ({$this->dsn}): " . $e->getMessage(), __CLASS__);
				$message = YII_DEBUG ? 'Failed to open DB connection: ' . $e->getMessage() : 'Failed to open DB connection.';
				throw new Exception($message, (int)$e->getCode(), $e->errorInfo);
			}
		}
	}

	/**
	 * Closes the currently active DB connection.
	 * It does nothing if the connection is already closed.
	 */
	public function close()
	{
		if ($this->redis !== null) {
			\Yii::trace('Closing DB connection: ' . $this->dsn, __CLASS__);
			$this->redis = null;
			$this->_transaction = null;
		}
	}

	/**
	 * Initializes the DB connection.
	 * This method is invoked right after the DB connection is established.
	 * The default implementation turns on `PDO::ATTR_EMULATE_PREPARES`
	 * if [[emulatePrepare]] is true, and sets the database [[charset]] if it is not empty.
	 * It then triggers an [[EVENT_AFTER_OPEN]] event.
	 */
	protected function initConnection()
	{
		$this->trigger(self::EVENT_AFTER_OPEN);
	}


	/**
	 * Creates a command for execution.
	 * @param string $query the SQL statement to be executed
	 * @param array $params the parameters to be bound to the SQL statement
	 * @return Command the DB command
	 */
	public function createCommand($query = null, $params = array())
	{
		$this->open();
		$command = new Command(array(
			'db' => $this,
			'query' => $query,
		));
		return $command->addValues($params);
	}

	/**
	 * Returns the currently active transaction.
	 * @return Transaction the currently active transaction. Null if no active transaction.
	 */
	public function getTransaction()
	{
		return $this->_transaction && $this->_transaction->isActive ? $this->_transaction : null;
	}

	/**
	 * Starts a transaction.
	 * @return Transaction the transaction initiated
	 */
	public function beginTransaction()
	{
		$this->open();
		$this->_transaction = new Transaction(array(
			'db' => $this,
		));
		$this->_transaction->begin();
		return $this->_transaction;
	}

	/**
	 * Returns the schema information for the database opened by this connection.
	 * @return Schema the schema information for the database opened by this connection.
	 * @throws NotSupportedException if there is no support for the current driver type
	 */
	public function getSchema()
	{
		$driver = $this->getDriverName();
		throw new NotSupportedException("Connection does not support reading schema information for '$driver' DBMS.");
	}

	/**
	 * Returns the query builder for the current DB connection.
	 * @return QueryBuilder the query builder for the current DB connection.
	 */
	public function getQueryBuilder()
	{
		return $this->getSchema()->getQueryBuilder();
	}

	/**
	 * Obtains the schema information for the named table.
	 * @param string $name table name.
	 * @param boolean $refresh whether to reload the table schema even if it is found in the cache.
	 * @return TableSchema table schema information. Null if the named table does not exist.
	 */
	public function getTableSchema($name, $refresh = false)
	{
		return $this->getSchema()->getTableSchema($name, $refresh);
	}

	/**
	 * Returns the name of the DB driver for the current [[dsn]].
	 * @return string name of the DB driver
	 */
	public function getDriverName()
	{
		if (($pos = strpos($this->dsn, ':')) !== false) {
			return strtolower(substr($this->dsn, 0, $pos));
		} else {
			return 'redis';
		}
	}

	/**
	 * Returns the statistical results of SQL queries.
	 * The results returned include the number of SQL statements executed and
	 * the total time spent.
	 * In order to use this method, [[enableProfiling]] has to be set true.
	 * @return array the first element indicates the number of SQL statements executed,
	 * and the second element the total time spent in SQL execution.
	 * @see \yii\logging\Logger::getProfiling()
	 */
	public function getQuerySummary()
	{
		$logger = \Yii::getLogger();
		$timings = $logger->getProfiling(array('yii\db\Command::query', 'yii\db\Command::execute'));
		$count = count($timings);
		$time = 0;
		foreach ($timings as $timing) {
			$time += $timing[1];
		}
		return array($count, $time);
	}
}
