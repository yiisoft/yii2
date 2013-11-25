<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongo;

use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use Yii;

/**
 * Class Connection
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Connection extends Component
{
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
	 * Establishes a Mongo connection.
	 * It does nothing if a Mongo connection has already been established.
	 * @throws Exception if connection fails
	 */
	public function open()
	{
		if ($this->client === null) {
			if (empty($this->dsn)) {
				throw new InvalidConfigException('Connection::dsn cannot be empty.');
			}
			$token = 'Opening Mongo connection: ' . $this->dsn;
			try {
				Yii::trace($token, __METHOD__);
				Yii::beginProfile($token, __METHOD__);
				$options = $this->options;
				$options['connect'] = true;
				$this->client = new \MongoClient($this->dsn, $options);
				$this->client->selectDB($this->dbName);
				Yii::endProfile($token, __METHOD__);
			} catch (\Exception $e) {
				Yii::endProfile($token, __METHOD__);
				throw new Exception($e->getMessage(), [], (int)$e->getCode(), $e);
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
		}
	}
}