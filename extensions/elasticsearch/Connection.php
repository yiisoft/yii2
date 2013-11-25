<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * elasticsearch Connection is used to connect to an elasticsearch cluster version 0.20 or higher
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
abstract class Connection extends Component
{
	/**
	 * @event Event an event that is triggered after a DB connection is established
	 */
	const EVENT_AFTER_OPEN = 'afterOpen';

	/**
	 * @var bool whether to autodetect available cluster nodes on [[open()]]
	 */
	public $autodetectCluster = true;
	/**
	 * @var array cluster nodes
	 * This is populated with the result of a cluster nodes request when [[autodetectCluster]] is true.
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/cluster-nodes-info.html#cluster-nodes-info
	 */
	public $nodes = [
		['http_address' => 'inet[/127.0.0.1:9200]'],
	];
	/**
	 * @var array the active node. key of [[nodes]]. Will be randomly selected on [[open()]].
	 */
	public $activeNode;

	// TODO http://www.elasticsearch.org/guide/en/elasticsearch/client/php-api/current/_configuration.html#_example_configuring_http_basic_auth
	public $auth = [];

	public function init()
	{
		foreach($this->nodes as $node) {
			if (!isset($node['http_address'])) {
				throw new InvalidConfigException('Elasticsearch node needs at least a http_address configured.');
			}
		}
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
		return $this->activeNode !== null;
	}

	/**
	 * Establishes a DB connection.
	 * It does nothing if a DB connection has already been established.
	 * @throws Exception if connection fails
	 */
	public function open()
	{
		if ($this->activeNode !== null) {
			return;
		}
		if (empty($this->nodes)) {
			throw new InvalidConfigException('elasticsearch needs at least one node to operate.');
		}
		if ($this->autodetectCluster) {
			$node = reset($this->nodes);
			$host = $node['http_address'];
			if (strncmp($host, 'inet[/', 6) == 0) {
				$host = substr($host, 6, -1);
			}
			$response = $this->httpRequest('get', 'http://' . $host . '/_cluster/nodes');
			$this->nodes = $response['nodes'];
			if (empty($this->nodes)) {
				throw new Exception('cluster autodetection did not find any active node.');
			}
		}
		$this->selectActiveNode();
		Yii::trace('Opening connection to elasticsearch. Nodes in cluster: ' . count($this->nodes)
			. ', active node: ' . $this->nodes[$this->activeNode]['http_address'], __CLASS__);
		$this->initConnection();
	}

	/**
	 * select active node randomly
	 */
	protected function selectActiveNode()
	{
		$keys = array_keys($this->nodes);
		$this->activeNode = $keys[rand(0, count($keys) - 1)];
	}

	/**
	 * Closes the currently active DB connection.
	 * It does nothing if the connection is already closed.
	 */
	public function close()
	{
		Yii::trace('Closing connection to elasticsearch. Active node was: '
			. $this->nodes[$this->activeNode]['http_address'], __CLASS__);
		$this->activeNode = null;
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

	/**
	 * Creates a command for execution.
	 * @param array $config the configuration for the Command class
	 * @return Command the DB command
	 */
	public function createCommand($config = [])
	{
		$this->open();
		$config['db'] = $this;
		$command = new Command($config);
		return $command;
	}

	public function getQueryBuilder()
	{
		return new QueryBuilder($this);
	}

	public function get($url, $options = [], $body = null, $validCodes = [])
	{
		$this->open();
		return $this->httpRequest('get', $this->createUrl($url, $options), $body);
	}

	public function head($url, $options = [], $body = null)
	{
		$this->open();
		return $this->httpRequest('head', $this->createUrl($url, $options), $body);
	}

	public function post($url, $options = [], $body = null)
	{
		$this->open();
		return $this->httpRequest('post', $this->createUrl($url, $options), $body);
	}

	public function put($url, $options = [], $body = null)
	{
		$this->open();
		return $this->httpRequest('put', $this->createUrl($url, $options), $body);
	}

	public function delete($url, $options = [], $body = null)
	{
		$this->open();
		return $this->httpRequest('delete', $this->createUrl($url, $options), $body);
	}

	private function createUrl($path, $options = [])
	{
		$url = implode('/', array_map(function($a) {
			return urlencode(is_array($a) ? implode(',', $a) : $a);
		}, $path));

		if (!empty($options)) {
			$url .= '?' . http_build_query($options);
		}

		return $url;
	}

	protected abstract function httpRequest($type, $url, $body = null);

	public function getNodeInfo()
	{
		return $this->get([]);
	}

	public function getClusterState()
	{
		return $this->get(['_cluster', 'state']);
	}
}