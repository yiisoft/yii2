<?php
/**
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yii\elasticsearch;


use yii\base\Component;
use yii\helpers\Json;

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

	private function createUrl($endPoint = null)
	{
		if ($endPoint === null) {
			$endPoint = $this->api;
		}
		if ($this->index === null && $this->type === null) {
			return '/' . $endPoint;
		}
		$index = $this->index;
		if ($index === null) {
			$index = '_all';
		} elseif (is_array($index)) {
			$index = implode(',', $index);
		}
		$type = $this->type;
		if (is_array($type)) {
			$type = implode(',', $type);
		}
		return '/' . $index . '/' . (empty($type) ? '' : $type . '/') . $endPoint;
	}

	public function queryAll()
	{
		$query = $this->query;
		if (empty($query)) {
			$query = '{}';
		}
		if (is_array($query)) {
			$query = Json::encode($query);
		}
		$http = $this->db->http();
		$response = $http->post($this->createUrl(), null, $query)->send();
		$data = Json::decode($response->getBody(true));
		// TODO store query meta data for later use
		$docs = array();
		switch ($this->api) {
			default:
			case '_search':
				if (isset($data['hits']['hits'])) {
					$docs = $data['hits']['hits'];
				}
				break;
			case '_mget':
				if (isset($data['docs'])) {
					$docs = $data['docs'];
				}
				break;
		}
		$rows = array();
		foreach($docs as $doc) {
			// TODO maybe return type info
			if (isset($doc['exists']) && !$doc['exists']) {
				continue;
			}
			$row = $doc['_source'];
			$row['id'] = $doc['_id'];
			$rows[] = $row;
		}
		return $rows;
	}

	public function queryOne()
	{
		// TODO set limit
		$rows = $this->queryAll();
		return reset($rows);
	}

	public function queryCount()
	{
		//http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-count.html
		$query = $this->query;
		if (empty($query)) {
			$query = '';
		}
		if (is_array($query)) {
			$query = Json::encode($query);
		}
		$http = $this->db->http();
		$response = $http->post($this->createUrl('_count'), null, $query)->send();
		$data = Json::decode($response->getBody(true));
		// TODO store query meta data for later use
		return $data['count'];
	}
}