<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;


use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * elasticsearch Connection is used to connect to an elasticsearch cluster version 0.20 or higher
 *
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Connection extends Component
{
	/**
	 * @event Event an event that is triggered after a DB connection is established
	 */
	const EVENT_AFTER_OPEN = 'afterOpen';

	// TODO add autodetection of cluster nodes
	// http://localhost:9200/_cluster/nodes
	public $nodes = array(
		array(
			'host' => 'localhost',
			'port' => 9200,
		)
	);

	// http://www.elasticsearch.org/guide/en/elasticsearch/client/php-api/current/_configuration.html#_example_configuring_http_basic_auth
	public $auth = [];

	// TODO use timeouts
	/**
	 * @var float timeout to use for connection to redis. If not set the timeout set in php.ini will be used: ini_get("default_socket_timeout")
	 */
	public $connectionTimeout = null;
	/**
	 * @var float timeout to use for redis socket when reading and writing data. If not set the php default value will be used.
	 */
	public $dataTimeout = null;



	public function init()
	{
		if ($this->nodes === array()) {
			throw new InvalidConfigException('elasticsearch needs at least one node.');
		}
	}

	/**
	 * Creates a command for execution.
	 * @param string $query the SQL statement to be executed
	 * @return Command the DB command
	 */
	public function createCommand($config = [])
	{
		$this->open();
		$config['db'] = $this;
		$command = new Command($config);
		return $command;
	}

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
		return false; // TODO implement
	}

	/**
	 * Establishes a DB connection.
	 * It does nothing if a DB connection has already been established.
	 * @throws Exception if connection fails
	 */
	public function open()
	{
		// TODO select one node to be the active one.


		foreach($this->nodes as $key => $node) {
			if (is_array($node)) {
				$this->nodes[$key] = new Node($node);
			}
		}
/*		if ($this->_socket === null) {
			if (empty($this->dsn)) {
				throw new InvalidConfigException('Connection.dsn cannot be empty.');
			}
			$dsn = explode('/', $this->dsn);
			$host = $dsn[2];
			if (strpos($host, ':')===false) {
				$host .= ':6379';
			}
			$db = isset($dsn[3]) ? $dsn[3] : 0;

			\Yii::trace('Opening DB connection: ' . $this->dsn, __CLASS__);
			$this->_socket = @stream_socket_client(
				$host,
				$errorNumber,
				$errorDescription,
				$this->connectionTimeout ? $this->connectionTimeout : ini_get("default_socket_timeout")
			);
			if ($this->_socket) {
				if ($this->dataTimeout !== null) {
					stream_set_timeout($this->_socket, $timeout=(int)$this->dataTimeout, (int) (($this->dataTimeout - $timeout) * 1000000));
				}
				if ($this->password !== null) {
					$this->executeCommand('AUTH', array($this->password));
				}
				$this->executeCommand('SELECT', array($db));
				$this->initConnection();
			} else {
				\Yii::error("Failed to open DB connection ({$this->dsn}): " . $errorNumber . ' - ' . $errorDescription, __CLASS__);
				$message = YII_DEBUG ? 'Failed to open DB connection: ' . $errorNumber . ' - ' . $errorDescription : 'Failed to open DB connection.';
				throw new Exception($message, $errorDescription, (int)$errorNumber);
			}
		}*/
		// TODO implement
	}

	/**
	 * Closes the currently active DB connection.
	 * It does nothing if the connection is already closed.
	 */
	public function close()
	{
		// TODO implement
/*		if ($this->_socket !== null) {
			\Yii::trace('Closing DB connection: ' . $this->dsn, __CLASS__);
			$this->executeCommand('QUIT');
			stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
			$this->_socket = null;
			$this->_transaction = null;
		}*/
	}

	/**
	 * Initializes the DB connection.
	 * This method is invoked right after the DB connection is established.
	 * The default implementation triggers an [[EVENT_AFTER_OPEN]] event.
	 */
	protected function initConnection()
	{
		$this->trigger(self::EVENT_AFTER_OPEN);
	}

	/**
	 * Returns the name of the DB driver for the current [[dsn]].
	 * @return string name of the DB driver
	 */
	public function getDriverName()
	{
		return 'elasticsearch';
	}

	public function getNodeInfo()
	{
		// TODO HTTP request to localhost:9200/
	}

	public function getQueryBuilder()
	{
		return new QueryBuilder($this);
	}

	/**
	 * @return \Guzzle\Http\Client
	 */
	public function http()
	{
		$guzzle = new \Guzzle\Http\Client('http://localhost:9200/');
		//$guzzle->setDefaultOption()
		return $guzzle;
	}
}