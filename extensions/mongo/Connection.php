<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongo;

use yii\base\Component;
use yii\base\InvalidConfigException;
use Yii;

/**
 * Class Connection
 *
 * @property boolean $isActive Whether the Mongo connection is established. This property is read-only.
 * @property QueryBuilder $queryBuilder The query builder for the current Mongo connection. This property
 * is read-only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Connection extends Component
{
	/**
	 * @var \MongoCollection[] list of Mongo collection available in database.
	 */
	private $_collections = [];

	/**
	 * @var \MongoClient mongo client instance.
	 */
	public $client;
	/**
	 * @var array connection options.
	 * for example:
	 * ~~~
	 * [
	 *     'persist' => true, // use persistent connection
	 *     'socketTimeoutMS' => 1000, // how long a send or receive on a socket can take before timing out
	 *     'journal' => true // block write operations until the journal be flushed the to disk
	 * ]
	 * ~~~
	 */
	public $options = [];
	/**
	 * @var string host:port
	 *
	 * Correct syntax is:
	 * mongodb://[username:password@]host1[:port1][,host2[:port2:],...]
	 * For example: mongodb://localhost:27017
	 */
	public $dsn;
	/**
	 * @var string name of the Mongo database to use
	 */
	public $dbName;
	/**
	 * @var \MongoDb Mongo database instance.
	 */
	public $db;

	/**
	 * Returns the Mongo collection with the given name.
	 * @param string $name collection name
	 * @param boolean $refresh whether to reload the table schema even if it is found in the cache.
	 * @return \MongoCollection mongo collection instance.
	 */
	public function getCollection($name, $refresh = false)
	{
		if ($refresh || !array_key_exists($name, $this->_collections)) {
			$this->_collections[$name] = $this->client->selectCollection($this->dbName, $name);
		}
		return $this->_collections[$name];
	}

	/**
	 * Returns a value indicating whether the Mongo connection is established.
	 * @return boolean whether the Mongo connection is established
	 */
	public function getIsActive()
	{
		return is_object($this->client) && $this->client->connected;
	}

	/**
	 * Establishes a Mongo connection.
	 * It does nothing if a Mongo connection has already been established.
	 * @throws Exception if connection fails
	 */
	public function open()
	{
		if ($this->client === null) {
			if (empty($this->dsn)) {
				throw new InvalidConfigException($this->className() . '::dsn cannot be empty.');
			}
			$token = 'Opening Mongo connection: ' . $this->dsn;
			try {
				Yii::trace($token, __METHOD__);
				Yii::beginProfile($token, __METHOD__);
				$options = $this->options;
				$options['connect'] = true;
				$options['db'] = $this->dbName;
				$this->client = new \MongoClient($this->dsn, $options);
				$this->db = $this->client->selectDB($this->dbName);
				Yii::endProfile($token, __METHOD__);
			} catch (\Exception $e) {
				Yii::endProfile($token, __METHOD__);
				throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
			}
		}
	}

	/**
	 * Closes the currently active DB connection.
	 * It does nothing if the connection is already closed.
	 */
	public function close()
	{
		if ($this->client !== null) {
			Yii::trace('Closing Mongo connection: ' . $this->dsn, __METHOD__);
			$this->client = null;
			$this->db = null;
		}
	}

	/**
	 * Returns the query builder for the current DB connection.
	 * @return QueryBuilder the query builder for the current DB connection.
	 */
	public function getQueryBuilder()
	{
		return new QueryBuilder($this);
	}

	/**
	 * Creates a command for execution.
	 * @return Command the Mongo command
	 */
	public function createCommand()
	{
		$this->open();
		$command = new Command([
			'db' => $this,
		]);
		return $command;
	}
}