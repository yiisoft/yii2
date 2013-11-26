<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongo;

use yii\base\Object;
use Yii;

/**
 * Collection represents the Mongo collection information.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Collection extends Object
{
	/**
	 * @var \MongoCollection Mongo collection instance.
	 */
	public $mongoCollection;

	/**
	 * Drops this collection.
	 */
	public function drop()
	{
		$this->mongoCollection->drop();
	}

	/**
	 * @param array $query
	 * @param array $fields
	 * @return \MongoCursor
	 */
	public function find($query = [], $fields = [])
	{
		return $this->mongoCollection->find($query, $fields);
	}

	/**
	 * @param array $query
	 * @param array $fields
	 * @return array
	 */
	public function findAll($query = [], $fields = [])
	{
		$cursor = $this->find($query, $fields);
		$result = [];
		foreach ($cursor as $data) {
			$result[] = $data;
		}
		return $result;
	}

	/**
	 * Inserts new data into collection.
	 * @param array|object $data data to be inserted.
	 * @param array $options list of options in format: optionName => optionValue.
	 * @return \MongoId new record id instance.
	 * @throws Exception on failure.
	 */
	public function insert($data, $options = [])
	{
		$token = 'Inserting data into ' . $this->mongoCollection->getName();
		Yii::info($token, __METHOD__);
		try {
			Yii::beginProfile($token, __METHOD__);
			$this->tryResultError($this->mongoCollection->insert($data, $options));
			Yii::endProfile($token, __METHOD__);
			return is_array($data) ? $data['_id'] : $data->_id;
		} catch (\Exception $e) {
			Yii::endProfile($token, __METHOD__);
			throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
		}
	}

	/**
	 * Update the existing database data, otherwise insert this data
	 * @param array|object $data data to be updated/inserted.
	 * @param array $options list of options in format: optionName => optionValue.
	 * @return \MongoId updated/new record id instance.
	 * @throws Exception on failure.
	 */
	public function save($data, $options = [])
	{
		$token = 'Saving data into ' . $this->mongoCollection->getName();
		Yii::info($token, __METHOD__);
		try {
			Yii::beginProfile($token, __METHOD__);
			$this->tryResultError($this->mongoCollection->save($data, $options));
			Yii::endProfile($token, __METHOD__);
			return is_array($data) ? $data['_id'] : $data->_id;
		} catch (\Exception $e) {
			Yii::endProfile($token, __METHOD__);
			throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
		}
	}

	/**
	 * Removes data from the collection.
	 * @param array $criteria description of records to remove.
	 * @param array $options list of options in format: optionName => optionValue.
	 * @return boolean whether operation was successful.
	 * @throws Exception on failure.
	 */
	public function remove($criteria = [], $options = [])
	{
		$token = 'Removing data from ' . $this->mongoCollection->getName();
		Yii::info($token, __METHOD__);
		try {
			Yii::beginProfile($token, __METHOD__);
			$this->tryResultError($this->mongoCollection->remove($criteria, $options));
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