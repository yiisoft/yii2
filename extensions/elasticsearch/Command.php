<?php
/**
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yii\elasticsearch;


use Guzzle\Http\Exception\ClientErrorResponseException;
use yii\base\Component;
use yii\db\Exception;
use yii\helpers\Json;

// camelCase vs. _
// http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/common-options.html#_result_casing


/**
 * Class Command
 *
 * http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/glossary.html
 *
 */
class Command extends Component
{
	/**
	 * @var Connection
	 */
	public $db;
	/**
	 * @var string|array the indexes to execute the query on. Defaults to null meaning all indexes
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search.html#search-multi-index
	 */
	public $index;
	/**
	 * @var string|array the types to execute the query on. Defaults to null meaning all types
	 */
	public $type;
	/**
	 * @var array list of arrays or json strings that become parts of a query
	 */
	public $queryParts;

	public $options = [];

	public function queryAll($options = [])
	{
		$query = $this->queryParts;
		if (empty($query)) {
			$query = '{}';
		}
		if (is_array($query)) {
			$query = Json::encode($query);
		}
		$url = [
			$this->index !== null ? $this->index : '_all',
			$this->type !== null ? $this->type : '_all',
			'_search'
		];
		try {
			$response = $this->db->http()->post($this->createUrl($url, $options), null, $query)->send();
		} catch(ClientErrorResponseException $e) {
			throw new Exception("elasticsearch error:\n\n"
				. $query . "\n\n" . $e->getMessage()
				. print_r(Json::decode($e->getResponse()->getBody(true)), true), [], 0, $e);
		}
		return Json::decode($response->getBody(true))['hits'];
	}

	public function queryCount($options = [])
	{
		$options['search_type'] = 'count';
		return $this->queryAll($options);
	}


	/**
	 * Inserts a document into an index
	 * @param string $index
	 * @param string $type
	 * @param string|array $data json string or array of data to store
	 * @param null $id the documents id. If not specified Id will be automatically choosen
	 * @param array $options
	 * @return mixed
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-index_.html
	 */
	public function insert($index, $type, $data, $id = null, $options = [])
	{
		$body = is_array($data) ? Json::encode($data) : $data;

		try {
			if ($id !== null) {
				$response = $this->db->http()->put($this->createUrl([$index, $type, $id], $options), null, $body)->send();
			} else {
				$response = $this->db->http()->post($this->createUrl([$index, $type], $options), null, $body)->send();
			}
		} catch(ClientErrorResponseException $e) {
			throw new Exception("elasticsearch error:\n\n"
				. $body . "\n\n" . $e->getMessage()
				. print_r(Json::decode($e->getResponse()->getBody(true)), true), [], 0, $e);
		}
		return Json::decode($response->getBody(true));
	}

	/**
	 * gets a document from the index
	 * @param $index
	 * @param $type
	 * @param $id
	 * @param array $options
	 * @return mixed
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-get.html
	 */
	public function get($index, $type, $id, $options = [])
	{
		$httpOptions = [
			'exceptions' => false,
		];
		$response = $this->db->http()->get($this->createUrl([$index, $type, $id], $options), null, $httpOptions)->send();
		if ($response->getStatusCode() == 200 || $response->getStatusCode() == 404) {
			return Json::decode($response->getBody(true));
		} else {
			throw new Exception('Elasticsearch request failed.');
		}
	}

	/**
	 * gets multiple documents from the index
	 *
	 * TODO allow specifying type and index + fields
	 * @param $index
	 * @param $type
	 * @param $id
	 * @param array $options
	 * @return mixed
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-get.html
	 */
	public function mget($index, $type, $ids, $options = [])
	{
		$httpOptions = [
			'exceptions' => false,
		];
		$body = Json::encode(['ids' => array_values($ids)]);
		$response = $this->db->http()->post( // TODO guzzle does not manage to send get request with content
			$this->createUrl([$index, $type, '_mget'], $options),
			null,
			$body,
			$httpOptions
		)->send();
		if ($response->getStatusCode() == 200) {
			return Json::decode($response->getBody(true));
		} else {
			throw new Exception('Elasticsearch request failed.');
		}
	}

	/**
	 * gets a documents _source from the index (>=v0.90.1)
	 * @param $index
	 * @param $type
	 * @param $id
	 * @return mixed
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-get.html#_source
	 */
	public function getSource($index, $type, $id)
	{
		$response = $this->db->http()->head($this->createUrl([$index, $type, $id]))->send();
		return Json::decode($response->getBody(true));
	}

	// TODO mget http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-multi-get.html

	/**
	 * gets a document from the index
	 * @param $index
	 * @param $type
	 * @param $id
	 * @return mixed
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-get.html
	 */
	public function exists($index, $type, $id)
	{
		$response = $this->db->http()->head($this->createUrl([$index, $type, $id]))->send();
		return $response->getStatusCode() == 200;
	}

	/**
	 * deletes a document from the index
	 * @param $index
	 * @param $type
	 * @param $id
	 * @param array $options
	 * @return mixed
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-delete.html
	 */
	public function delete($index, $type, $id, $options = [])
	{
		$response = $this->db->http()->delete($this->createUrl([$index, $type, $id], $options))->send();
		return Json::decode($response->getBody(true));
	}

	/**
	 * updates a document
	 * @param $index
	 * @param $type
	 * @param $id
	 * @param array $options
	 * @return mixed
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-update.html
	 */
	public function update($index, $type, $id, $data, $options = [])
	{
		// TODO
		$response = $this->db->http()->delete($this->createUrl([$index, $type, $id], $options))->send();
		return Json::decode($response->getBody(true));
	}

	// TODO bulk http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-bulk.html


	/**
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-create-index.html
	 */
	public function createIndex($index, $configuration = null)
	{
		$body = $configuration !== null ? Json::encode($configuration) : null;
		$response = $this->db->http()->put($this->createUrl([$index]), null, $body)->send();
		return Json::decode($response->getBody(true));
	}

	/**
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-delete-index.html
	 */
	public function deleteIndex($index)
	{
		$response = $this->db->http()->delete($this->createUrl([$index]))->send();
		return Json::decode($response->getBody(true));
	}

	/**
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-delete-index.html
	 */
	public function deleteAllIndexes()
	{
		$response = $this->db->http()->delete($this->createUrl(['_all']))->send();
		return Json::decode($response->getBody(true));
	}

	/**
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-exists.html
	 */
	public function indexExists($index)
	{
		$response = $this->db->http()->head($this->createUrl([$index]))->send();
		return $response->getStatusCode() == 200;
	}

	/**
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-types-exists.html
	 */
	public function typeExists($index, $type)
	{
		$response = $this->db->http()->head($this->createUrl([$index, $type]))->send();
		return $response->getStatusCode() == 200;
	}

	// TODO http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-aliases.html

	// TODO http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-update-settings.html
	// TODO http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-get-settings.html

	// TODO http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-warmers.html

	/**
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-open-close.html
	 */
	public function openIndex($index)
	{
		$response = $this->db->http()->post($this->createUrl([$index, '_open']))->send();
		return $response->getStatusCode() == 200;
	}

	/**
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-open-close.html
	 */
	public function closeIndex($index)
	{
		$response = $this->db->http()->post($this->createUrl([$index, '_close']))->send();
		return $response->getStatusCode() == 200;
	}

	/**
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-status.html
	 */
	public function getIndexStatus($index = '_all')
	{
		$response = $this->db->http()->get($this->createUrl([$index, '_status']))->send();
		return Json::decode($response->getBody(true));
	}

	// TODO http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-stats.html
	// http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-segments.html

	/**
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-clearcache.html
	 */
	public function clearIndexCache($index)
	{
		$response = $this->db->http()->post($this->createUrl([$index, '_cache', 'clear']))->send();
		return $response->getStatusCode() == 200;
	}

	/**
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-flush.html
	 */
	public function flushIndex($index = '_all')
	{
		$response = $this->db->http()->post($this->createUrl([$index, '_flush']))->send();
		return $response->getStatusCode() == 200;
	}

	/**
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-refresh.html
	 */
	public function refreshIndex($index)
	{
		$response = $this->db->http()->post($this->createUrl([$index, '_refresh']))->send();
		return $response->getStatusCode() == 200;
	}

	// TODO http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-optimize.html

	// TODO http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-gateway-snapshot.html

	/**
	 * http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-put-mapping.html
	 */
	public function setMapping($index, $type, $mapping)
	{
		$body = $mapping !== null ? Json::encode($mapping) : null;
		$response = $this->db->http()->put($this->createUrl([$index, $type, '_mapping']), null, $body)->send();
		return $response->getStatusCode() == 200;
	}

	/**
	 * http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-get-mapping.html
	 */
	public function getMapping($index = '_all', $type = '_all')
	{
		$response = $this->db->http()->get($this->createUrl([$index, $type, '_mapping']))->send();
		return Json::decode($response->getBody(true));
	}

	/**
	 * http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-put-mapping.html
	 */
	public function deleteMapping($index, $type)
	{
		$response = $this->db->http()->delete($this->createUrl([$index, $type]))->send();
		return $response->getStatusCode() == 200;
	}

	/**
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-get-field-mapping.html
	 */
	public function getFieldMapping($index, $type = '_all')
	{
		// TODO
		$response = $this->db->http()->put($this->createUrl([$index, $type, '_mapping']))->send();
		return Json::decode($response->getBody(true));
	}

	/**
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-analyze.html
	 */
	public function analyze($options, $index = null)
	{
		// TODO
		$response = $this->db->http()->put($this->createUrl([$index, $type, '_mapping']))->send();
		return Json::decode($response->getBody(true));

	}

	/**
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-templates.html
	 */
	public function createTemplate($name, $pattern, $settings, $mappings, $order = 0)
	{
		$body = Json::encode([
			'template' => $pattern,
			'order' => $order,
			'settings' => (object) $settings,
			'mappings' => (object) $settings,
		]);
		$response = $this->db->http()->put($this->createUrl(['_template', $name]), null, $body)->send();
		return $response->getStatusCode() == 200;
	}

	/**
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-templates.html
	 */
	public function deleteTemplate($name)
	{
		$response = $this->db->http()->delete($this->createUrl(['_template', $name]))->send();
		return $response->getStatusCode() == 200;
	}

	/**
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-templates.html
	 */
	public function getTemplate($name)
	{
		$response = $this->db->http()->get($this->createUrl(['_template', $name]))->send();
		return Json::decode($response->getBody(true));
	}

	private function createUrl($path, $options = [])
	{
		$url = implode('/', array_map(function($a) {
			return urlencode(is_array($a) ? implode(',', $a) : $a);
		}, $path));

		if (!empty($options) || !empty($this->options)) {
			$options = array_merge($this->options, $options);
			$url .= '?' . http_build_query($options);
		}

		return $url;
	}
}