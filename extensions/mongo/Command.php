<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongo;

use \yii\base\Component;
use Yii;

/**
 * Class Command
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Command extends Component
{
	/**
	 * @var Connection the Mongo connection that this command is associated with
	 */
	public $db;

	/**
	 * Drops the current database
	 */
	public function dropDb()
	{
		$this->db->db->drop();
	}

	/**
	 * Drops the specified collection.
	 * @param string $name collection name.
	 */
	public function dropCollection($name)
	{
		$collection = $this->db->getCollection($name);
		$collection->drop();
	}

	/**
	 * @param $collection
	 * @param array $query
	 * @param array $fields
	 * @return \MongoCursor
	 */
	public function find($collection, $query = [], $fields = [])
	{
		$collection = $this->db->getCollection($collection);
		return $collection->find($query, $fields);
	}

	/**
	 * @param $collection
	 * @param array $query
	 * @param array $fields
	 * @return array
	 */
	public function findAll($collection, $query = [], $fields = [])
	{
		$cursor = $this->find($collection, $query, $fields);
		$result = [];
		foreach ($cursor as $data) {
			$result[] = $data;
		}
		return $result;
	}

	/**
	 * Inserts new data into collection.
	 * @param string $collection name of the collection.
	 * @param array|object $data data to be inserted.
	 * @param array $options list of options in format: optionName => optionValue.
	 * @return \MongoId new record id instance.
	 * @throws Exception on failure.
	 */
	public function insert($collection, $data, $options = [])
	{
		$token = 'Inserting data into ' . $collection;
		Yii::info($token, __METHOD__);
		try {
			Yii::beginProfile($token, __METHOD__);
			$collection = $this->db->getCollection($collection);
			$this->tryResultError($collection->insert($data, $options));
			Yii::endProfile($token, __METHOD__);
			return is_array($data) ? $data['_id'] : $data->_id;
		} catch (\Exception $e) {
			Yii::endProfile($token, __METHOD__);
			throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
		}
	}

	/**
	 * Update the existing database data, otherwise insert this data
	 * @param string $collection name of the collection.
	 * @param array|object $data data to be updated/inserted.
	 * @param array $options list of options in format: optionName => optionValue.
	 * @return \MongoId updated/new record id instance.
	 * @throws Exception on failure.
	 */
	public function save($collection, $data, $options = [])
	{
		$token = 'Saving data into ' . $collection;
		Yii::info($token, __METHOD__);
		try {
			Yii::beginProfile($token, __METHOD__);
			$collection = $this->db->getCollection($collection);
			$this->tryResultError($collection->save($data, $options));
			Yii::endProfile($token, __METHOD__);
			return is_array($data) ? $data['_id'] : $data->_id;
		} catch (\Exception $e) {
			Yii::endProfile($token, __METHOD__);
			throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
		}
	}

	/**
	 * Removes data from the collection.
	 * @param string $collection name of the collection.
	 * @param array $criteria description of records to remove.
	 * @param array $options list of options in format: optionName => optionValue.
	 * @return boolean whether operation was successful.
	 * @throws Exception on failure.
	 */
	public function remove($collection, $criteria = [], $options = [])
	{
		$token = 'Removing data from ' . $collection;
		Yii::info($token, __METHOD__);
		try {
			Yii::beginProfile($token, __METHOD__);
			$collection = $this->db->getCollection($collection);
			$this->tryResultError($collection->remove($criteria, $options));
			Yii::endProfile($token, __METHOD__);
			return true;
		} catch (\Exception $e) {
			Yii::endProfile($token, __METHOD__);
			throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
		}
	}

	/**
	 * Checks if command execution result ended with an error.
	 * @param mixed $result raw command execution result.
	 * @throws Exception if an error occurred.
	 */
	protected function tryResultError($result)
	{
		if (is_array($result)) {
			if (!empty($result['err'])) {
				throw new Exception($result['errmsg'], (int)$result['code']);
			}
		} elseif (!$result) {
			throw new Exception('Unknown error, use "w=1" option to enable error tracking');
		}
	}
}