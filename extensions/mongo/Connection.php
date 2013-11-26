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
	 * @var string host:port
	 *
	 * Correct syntax is:
	 * mongodb://[username:password@]host1[:port1][,host2[:port2:],...][/dbname]
	 * For example:
	 * mongodb://localhost:27017
	 * mongodb://developer:somepassword@localhost:27017
	 * mongodb://developer:somepassword@localhost:27017/mydatabase
	 */
	public $dsn;
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
	 * @var string name of the Mongo database to use by default.
	 */
	public $defaultDatabaseName;
	/**
	 * @var \MongoClient mongo client instance.
	 */
	public $mongoClient;
	/**
	 * @var Database[] list of Mongo databases
	 */
	private $_databases = [];

	/**
	 * Returns the Mongo collection with the given name.
	 * @param string|null $name collection name, if null default one will be used.
	 * @param boolean $refresh whether to reload the table schema even if it is found in the cache.
	 * @return Database database instance.
	 */
	public function getDatabase($name = null, $refresh = false)
	{
		if ($name === null) {
			$name = $this->fetchDefaultDatabaseName();
		}
		if ($refresh || !array_key_exists($name, $this->_databases)) {
			$this->_databases[$name] = $this->selectDatabase($name);
		}
		return $this->_databases[$name];
	}

	/**
	 * Returns [[defaultDatabaseName]] value, if it is not set,
	 * attempts to determine it from [[dsn]] value.
	 * @return string default database name
	 * @throws \yii\base\InvalidConfigException if unable to determine default database name.
	 */
	protected function fetchDefaultDatabaseName()
	{
		if ($this->defaultDatabaseName === null) {
			if (preg_match('/^mongodb:\\/\\/.+\\/(.+)$/s', $this->dsn, $matches)) {
				$this->defaultDatabaseName = $matches[1];
			} else {
				throw new InvalidConfigException("Unable to determine default database name from dsn.");
			}
		}
		return $this->defaultDatabaseName;
	}

	/**
	 * Selects the database with given name.
	 * @param string $name database name.
	 * @return Database database instance.
	 */
	protected function selectDatabase($name)
	{
		$this->open();
		return Yii::createObject([
			'class' => 'yii\mongo\Database',
			'mongoDb' => $this->mongoClient->selectDB($name)
		]);
	}

	/**
	 * Returns the Mongo collection with the given name.
	 * @param string|array $name collection name. If string considered as  the name of the collection
	 * inside the default database. If array - first element considered as the name of the database,
	 * second - as name of collection inside that database
	 * @param boolean $refresh whether to reload the table schema even if it is found in the cache.
	 * @return Collection Mongo collection instance.
	 */
	public function getCollection($name, $refresh = false)
	{
		if (is_array($name)) {
			list ($dbName, $collectionName) = $name;
			return $this->getDatabase($dbName)->getCollection($collectionName, $refresh);
		} else {
			return $this->getDatabase()->getCollection($name, $refresh);
		}
	}

	/**
	 * Returns a value indicating whether the Mongo connection is established.
	 * @return boolean whether the Mongo connection is established
	 */
	public function getIsActive()
	{
		return is_object($this->mongoClient) && $this->mongoClient->connected;
	}

	/**
	 * Establishes a Mongo connection.
	 * It does nothing if a Mongo connection has already been established.
	 * @throws Exception if connection fails
	 */
	public function open()
	{
		if ($this->mongoClient === null) {
			if (empty($this->dsn)) {
				throw new InvalidConfigException($this->className() . '::dsn cannot be empty.');
			}
			$token = 'Opening Mongo connection: ' . $this->dsn;
			try {
				Yii::trace($token, __METHOD__);
				Yii::beginProfile($token, __METHOD__);
				$options = $this->options;
				$options['connect'] = true;
				if ($this->defaultDatabaseName !== null) {
					$options['db'] = $this->defaultDatabaseName;
				}
				$this->mongoClient = new \MongoClient($this->dsn, $options);
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
		if ($this->mongoClient !== null) {
			Yii::trace('Closing Mongo connection: ' . $this->dsn, __METHOD__);
			$this->mongoClient = null;
			$this->_databases = [];
		}
	}
}