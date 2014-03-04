<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongodb;

use yii\base\Component;
use yii\base\InvalidConfigException;
use Yii;

/**
 * Connection represents a connection to a MongoDb server.
 *
 * Connection works together with [[Database]] and [[Collection]] to provide data access
 * to the Mongo database. They are wrappers of the [[MongoDB PHP extension]](http://us1.php.net/manual/en/book.mongo.php).
 *
 * To establish a DB connection, set [[dsn]] and then call [[open()]] to be true.
 *
 * The following example shows how to create a Connection instance and establish
 * the DB connection:
 *
 * ~~~
 * $connection = new \yii\mongodb\Connection([
 *     'dsn' => $dsn,
 * ]);
 * $connection->open();
 * ~~~
 *
 * After the Mongo connection is established, one can access Mongo databases and collections:
 *
 * ~~~
 * $database = $connection->getDatabase('my_mongo_db');
 * $collection = $database->getCollection('customer');
 * $collection->insert(['name' => 'John Smith', 'status' => 1]);
 * ~~~
 *
 * You can work with several different databases at the same server using this class.
 * However, while it is unlikely your application will actually need it, the Connection class
 * provides ability to use [[defaultDatabaseName]] as well as a shortcut method [[getCollection()]]
 * to retrieve a particular collection instance:
 *
 * ~~~
 * // get collection 'customer' from default database:
 * $collection = $connection->getCollection('customer');
 * // get collection 'customer' from database 'mydatabase':
 * $collection = $connection->getCollection(['mydatabase', 'customer']);
 * ~~~
 *
 * Connection is often used as an application component and configured in the application
 * configuration like the following:
 *
 * ~~~
 * [
 *	 'components' => [
 *		 'mongodb' => [
 *			 'class' => '\yii\mongodb\Connection',
 *			 'dsn' => 'mongodb://developer:password@localhost:27017/mydatabase',
 *		 ],
 *	 ],
 * ]
 * ~~~
 *
 * @property Database $database Database instance. This property is read-only.
 * @property file\Collection $fileCollection Mongo GridFS collection instance. This property is read-only.
 * @property boolean $isActive Whether the Mongo connection is established. This property is read-only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Connection extends Component
{
	/**
	 * @event Event an event that is triggered after a DB connection is established
	 */
	const EVENT_AFTER_OPEN = 'afterOpen';

	/**
	 * @var string host:port
	 *
	 * Correct syntax is:
	 * mongodb://[username:password@]host1[:port1][,host2[:port2:],...][/dbname]
	 * For example:
	 * mongodb://localhost:27017
	 * mongodb://developer:password@localhost:27017
	 * mongodb://developer:password@localhost:27017/mydatabase
	 */
	public $dsn;
	/**
	 * @var array connection options.
	 * for example:
	 *
	 * ~~~
	 * [
	 *     'socketTimeoutMS' => 1000, // how long a send or receive on a socket can take before timing out
	 *     'journal' => true // block write operations until the journal be flushed the to disk
	 * ]
	 * ~~~
	 *
	 * @see http://www.php.net/manual/en/mongoclient.construct.php
	 */
	public $options = [];
	/**
	 * @var string name of the Mongo database to use by default.
	 * If this field left blank, connection instance will attempt to determine it from
	 * [[options]] and [[dsn]] automatically, if needed.
	 */
	public $defaultDatabaseName;
	/**
	 * @var \MongoClient Mongo client instance.
	 */
	public $mongoClient;
	/**
	 * @var Database[] list of Mongo databases
	 */
	private $_databases = [];

	/**
	 * Returns the Mongo collection with the given name.
	 * @param string|null $name collection name, if null default one will be used.
	 * @param boolean $refresh whether to reestablish the database connection even if it is found in the cache.
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
			if (isset($this->options['db'])) {
				$this->defaultDatabaseName = $this->options['db'];
			} elseif (preg_match('/^mongodb:\\/\\/.+\\/(.+)$/s', $this->dsn, $matches)) {
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
			'class' => 'yii\mongodb\Database',
			'mongoDb' => $this->mongoClient->selectDB($name)
		]);
	}

	/**
	 * Returns the Mongo collection with the given name.
	 * @param string|array $name collection name. If string considered as the name of the collection
	 * inside the default database. If array - first element considered as the name of the database,
	 * second - as name of collection inside that database
	 * @param boolean $refresh whether to reload the collection instance even if it is found in the cache.
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
	 * Returns the Mongo GridFS collection.
	 * @param string|array $prefix collection prefix. If string considered as the prefix of the GridFS
	 * collection inside the default database. If array - first element considered as the name of the database,
	 * second - as prefix of the GridFS collection inside that database, if no second element present
	 * default "fs" prefix will be used.
	 * @param boolean $refresh whether to reload the collection instance even if it is found in the cache.
	 * @return file\Collection Mongo GridFS collection instance.
	 */
	public function getFileCollection($prefix = 'fs', $refresh = false)
	{
		if (is_array($prefix)) {
			list ($dbName, $collectionPrefix) = $prefix;
			if (!isset($collectionPrefix)) {
				$collectionPrefix = 'fs';
			}
			return $this->getDatabase($dbName)->getFileCollection($collectionPrefix, $refresh);
		} else {
			return $this->getDatabase()->getFileCollection($prefix, $refresh);
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
			$token = 'Opening MongoDB connection: ' . $this->dsn;
			try {
				Yii::trace($token, __METHOD__);
				Yii::beginProfile($token, __METHOD__);
				$options = $this->options;
				$options['connect'] = true;
				if ($this->defaultDatabaseName !== null) {
					$options['db'] = $this->defaultDatabaseName;
				}
				$this->mongoClient = new \MongoClient($this->dsn, $options);
				$this->initConnection();
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
			Yii::trace('Closing MongoDB connection: ' . $this->dsn, __METHOD__);
			$this->mongoClient = null;
			$this->_databases = [];
		}
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
}
