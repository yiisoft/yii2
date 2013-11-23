<?php
/**
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yii\elasticsearch;


use yii\base\Component;
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

	public $api = '_search';

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
	 * @var array|string array or json
	 */
	public $query;

//	private function createUrl($endPoint = null)
//	{
//		if ($endPoint === null) {
//			$endPoint = $this->api;
//		}
//		if ($this->index === null && $this->type === null) {
//			return '/' . $endPoint;
//		}
//		$index = $this->index;
//		if ($index === null) {
//			$index = '_all';
//		} elseif (is_array($index)) {
//			$index = implode(',', $index);
//		}
//		$type = $this->type;
//		if (is_array($type)) {
//			$type = implode(',', $type);
//		}
//		return '/' . $index . '/' . (empty($type) ? '' : $type . '/') . $endPoint;
//	}
//
//	public function queryAll()
//	{
//		$query = $this->query;
//		if (empty($query)) {
//			$query = '{}';
//		}
//		if (is_array($query)) {
//			$query = Json::encode($query);
//		}
//		$http = $this->db->http();
//		$response = $http->post($this->createUrl(), null, $query)->send();
//		$data = Json::decode($response->getBody(true));
//		// TODO store query meta data for later use
//		$docs = array();
//		switch ($this->api) {
//			default:
//			case '_search':
//				if (isset($data['hits']['hits'])) {
//					$docs = $data['hits']['hits'];
//				}
//				break;
//			case '_mget':
//				if (isset($data['docs'])) {
//					$docs = $data['docs'];
//				}
//				break;
//		}
//		$rows = array();
//		foreach($docs as $doc) {
//			// TODO maybe return type info
//			if (isset($doc['exists']) && !$doc['exists']) {
//				continue;
//			}
//			$row = $doc['_source'];
//			$row['id'] = $doc['_id'];
//			$rows[] = $row;
//		}
//		return $rows;
//	}
//
//	public function queryOne()
//	{
//		// TODO set limit
//		$rows = $this->queryAll();
//		return reset($rows);
//	}
//
//	public function queryCount()
//	{
//		//http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-count.html
//		$query = $this->query;
//		if (empty($query)) {
//			$query = '';
//		}
//		if (is_array($query)) {
//			$query = Json::encode($query);
//		}
//		$http = $this->db->http();
//		$response = $http->post($this->createUrl('_count'), null, $query)->send();
//		$data = Json::decode($response->getBody(true));
//		// TODO store query meta data for later use
//		return $data['count'];
//	}


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
		if ($id !== null) {
			$response = $this->db->http()->put($this->createUrl([$index, $type, $id], $options), null, $body)->send();
		} else {
			$response = $this->db->http()->post($this->createUrl([$index, $type], $options), null, $body)->send();
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
		$response = $this->db->http()->post($this->createUrl([$index, $type, $id], $options))->send();
		return Json::decode($response->getBody(true));
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
	public function flushIndex($index)
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

		if (!empty($options)) {
			$url .= '?' . http_build_query($options);
		}

		return $url;
	}
}