<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Json;

/**
 * elasticsearch Connection is used to connect to an elasticsearch cluster version 0.20 or higher
 *
 * @property string $driverName Name of the DB driver. This property is read-only.
 * @property boolean $isActive Whether the DB connection is established. This property is read-only.
 * @property QueryBuilder $queryBuilder This property is read-only.
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

    /**
     * @var boolean whether to autodetect available cluster nodes on [[open()]]
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
    /**
     * @var float timeout to use for connecting to an elasticsearch node.
     * This value will be used to configure the curl `CURLOPT_CONNECTTIMEOUT` option.
     * If not set, no explicit timeout will be set for curl.
     */
    public $connectionTimeout = null;
    /**
     * @var float timeout to use when reading the response from an elasticsearch node.
     * This value will be used to configure the curl `CURLOPT_TIMEOUT` option.
     * If not set, no explicit timeout will be set for curl.
     */
    public $dataTimeout = null;

    public function init()
    {
        foreach ($this->nodes as $node) {
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
            $response = $this->httpRequest('GET', 'http://' . $host . '/_nodes');
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

    /**
     * Creates new query builder instance
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return new QueryBuilder($this);
    }

    /**
     * Performs GET HTTP request
     *
     * @param string $url URL
     * @param array $options URL options
     * @param string $body request body
     * @param boolean $raw if response body contains JSON and should be decoded
     * @return mixed response
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function get($url, $options = [], $body = null, $raw = false)
    {
        $this->open();

        return $this->httpRequest('GET', $this->createUrl($url, $options), $body, $raw);
    }

    /**
     * Performs HEAD HTTP request
     *
     * @param string $url URL
     * @param array $options URL options
     * @param string $body request body
     * @return mixed response
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function head($url, $options = [], $body = null)
    {
        $this->open();

        return $this->httpRequest('HEAD', $this->createUrl($url, $options), $body);
    }

    /**
     * Performs POST HTTP request
     *
     * @param string $url URL
     * @param array $options URL options
     * @param string $body request body
     * @param boolean $raw if response body contains JSON and should be decoded
     * @return mixed response
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function post($url, $options = [], $body = null, $raw = false)
    {
        $this->open();

        return $this->httpRequest('POST', $this->createUrl($url, $options), $body, $raw);
    }

    /**
     * Performs PUT HTTP request
     *
     * @param string $url URL
     * @param array $options URL options
     * @param string $body request body
     * @param boolean $raw if response body contains JSON and should be decoded
     * @return mixed response
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function put($url, $options = [], $body = null, $raw = false)
    {
        $this->open();

        return $this->httpRequest('PUT', $this->createUrl($url, $options), $body, $raw);
    }

    /**
     * Performs DELETE HTTP request
     *
     * @param string $url URL
     * @param array $options URL options
     * @param string $body request body
     * @param boolean $raw if response body contains JSON and should be decoded
     * @return mixed response
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function delete($url, $options = [], $body = null, $raw = false)
    {
        $this->open();

        return $this->httpRequest('DELETE', $this->createUrl($url, $options), $body, $raw);
    }

    /**
     * Creates URL
     *
     * @param mixed $path path
     * @param array $options URL options
     * @return array
     */
    private function createUrl($path, $options = [])
    {
        if (!is_string($path)) {
            $url = implode('/', array_map(function ($a) {
                return urlencode(is_array($a) ? implode(',', $a) : $a);
            }, $path));
            if (!empty($options)) {
                $url .= '?' . http_build_query($options);
            }
        } else {
            $url = $path;
            if (!empty($options)) {
                $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($options);
            }
        }

        return [$this->nodes[$this->activeNode]['http_address'], $url];
    }

    /**
     * Performs HTTP request
     *
     * @param string $method method name
     * @param string $url URL
     * @param string $requestBody request body
     * @param boolean $raw if response body contains JSON and should be decoded
     * @throws Exception if request failed
     * @throws \yii\base\InvalidParamException
     * @return mixed response
     */
    protected function httpRequest($method, $url, $requestBody = null, $raw = false)
    {
        $method = strtoupper($method);

        // response body and headers
        $headers = [];
        $body = '';

        $options = [
            CURLOPT_USERAGENT      => 'Yii Framework 2 ' . __CLASS__,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HEADER         => false,
            // http://www.php.net/manual/en/function.curl-setopt.php#82418
            CURLOPT_HTTPHEADER     => ['Expect:'],

            CURLOPT_WRITEFUNCTION  => function ($curl, $data) use (&$body) {
                $body .= $data;

                return mb_strlen($data, '8bit');
            },
            CURLOPT_HEADERFUNCTION => function ($curl, $data) use (&$headers) {
                foreach (explode("\r\n", $data) as $row) {
                    if (($pos = strpos($row, ':')) !== false) {
                        $headers[strtolower(substr($row, 0, $pos))] = trim(substr($row, $pos + 1));
                    }
                }

                return mb_strlen($data, '8bit');
            },
            CURLOPT_CUSTOMREQUEST  => $method,
        ];
        if ($this->connectionTimeout !== null) {
            $options[CURLOPT_CONNECTTIMEOUT] = $this->connectionTimeout;
        }
        if ($this->dataTimeout !== null) {
            $options[CURLOPT_TIMEOUT] = $this->dataTimeout;
        }
        if ($requestBody !== null) {
            $options[CURLOPT_POSTFIELDS] = $requestBody;
        }
        if ($method == 'HEAD') {
            $options[CURLOPT_NOBODY] = true;
            unset($options[CURLOPT_WRITEFUNCTION]);
        }

        if (is_array($url)) {
            list($host, $q) = $url;
            if (strncmp($host, 'inet[', 5) == 0) {
                $host = substr($host, 5, -1);
                if (($pos = strpos($host, '/')) !== false) {
                    $host = substr($host, $pos + 1);
                }
            }
            $profile = $method . ' ' . $q . '#' . $requestBody;
            $url = 'http://' . $host . '/' . $q;
        } else {
            $profile = false;
        }

        Yii::trace("Sending request to elasticsearch node: $url\n$requestBody", __METHOD__);
        if ($profile !== false) {
            Yii::beginProfile($profile, __METHOD__);
        }

        $curl = curl_init($url);
        curl_setopt_array($curl, $options);
        if (curl_exec($curl) === false) {
            throw new Exception('Elasticsearch request failed: ' . curl_errno($curl) . ' - ' . curl_error($curl), [
                'requestMethod' => $method,
                'requestUrl' => $url,
                'requestBody' => $requestBody,
                'responseHeaders' => $headers,
                'responseBody' => $body,
            ]);
        }

        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($profile !== false) {
            Yii::endProfile($profile, __METHOD__);
        }

        if ($responseCode >= 200 && $responseCode < 300) {
            if ($method == 'HEAD') {
                return true;
            } else {
                if (isset($headers['content-length']) && ($len = mb_strlen($body, '8bit')) < $headers['content-length']) {
                    throw new Exception("Incomplete data received from elasticsearch: $len < {$headers['content-length']}", [
                        'requestMethod' => $method,
                        'requestUrl' => $url,
                        'requestBody' => $requestBody,
                        'responseCode' => $responseCode,
                        'responseHeaders' => $headers,
                        'responseBody' => $body,
                    ]);
                }
                if (isset($headers['content-type']) && !strncmp($headers['content-type'], 'application/json', 16)) {
                    return $raw ? $body : Json::decode($body);
                }
                throw new Exception('Unsupported data received from elasticsearch: ' . $headers['content-type'], [
                    'requestMethod' => $method,
                    'requestUrl' => $url,
                    'requestBody' => $requestBody,
                    'responseCode' => $responseCode,
                    'responseHeaders' => $headers,
                    'responseBody' => $body,
                ]);
            }
        } elseif ($responseCode == 404) {
            return false;
        } else {
            throw new Exception("Elasticsearch request failed with code $responseCode.", [
                'requestMethod' => $method,
                'requestUrl' => $url,
                'requestBody' => $requestBody,
                'responseCode' => $responseCode,
                'responseHeaders' => $headers,
                'responseBody' => $body,
            ]);
        }
    }

    public function getNodeInfo()
    {
        return $this->get([]);
    }

    public function getClusterState()
    {
        return $this->get(['_cluster', 'state']);
    }
}
