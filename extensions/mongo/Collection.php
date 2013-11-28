<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongo;

use yii\base\InvalidParamException;
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
	 * @param array $condition
	 * @param array $fields
	 * @return \MongoCursor
	 */
	public function find($condition = [], $fields = [])
	{
		return $this->mongoCollection->find($this->buildCondition($condition), $fields);
	}

	/**
	 * @param array $condition
	 * @param array $fields
	 * @return array
	 */
	public function findAll($condition = [], $fields = [])
	{
		$cursor = $this->find($condition, $fields);
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
	 * Inserts several new rows into collection.
	 * @param array $rows array of arrays or objects to be inserted.
	 * @param array $options list of options in format: optionName => optionValue.
	 * @return array inserted data, each row will have "_id" key assigned to it.
	 * @throws Exception on failure.
	 */
	public function batchInsert($rows, $options = [])
	{
		$token = 'Inserting batch data into ' . $this->mongoCollection->getName();
		Yii::info($token, __METHOD__);
		try {
			Yii::beginProfile($token, __METHOD__);
			$this->tryResultError($this->mongoCollection->batchInsert($rows, $options));
			Yii::endProfile($token, __METHOD__);
			return $rows;
		} catch (\Exception $e) {
			Yii::endProfile($token, __METHOD__);
			throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
		}
	}

	/**
	 * Updates the rows, which matches given criteria by given data.
	 * @param array $condition description of the objects to update.
	 * @param array $newData the object with which to update the matching records.
	 * @param array $options list of options in format: optionName => optionValue.
	 * @return boolean whether operation was successful.
	 * @throws Exception on failure.
	 */
	public function update($condition, $newData, $options = [])
	{
		$token = 'Updating data in ' . $this->mongoCollection->getName();
		Yii::info($token, __METHOD__);
		try {
			Yii::beginProfile($token, __METHOD__);
			$this->mongoCollection->update($this->buildCondition($condition), $newData, $options);
			Yii::endProfile($token, __METHOD__);
			return true;
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
	 * @param array $condition description of records to remove.
	 * @param array $options list of options in format: optionName => optionValue.
	 * @return boolean whether operation was successful.
	 * @throws Exception on failure.
	 */
	public function remove($condition = [], $options = [])
	{
		$token = 'Removing data from ' . $this->mongoCollection->getName();
		Yii::info($token, __METHOD__);
		try {
			Yii::beginProfile($token, __METHOD__);
			$this->tryResultError($this->mongoCollection->remove($this->buildCondition($condition), $options));
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

	/**
	 * Converts user friendly condition keyword into actual Mongo condition keyword.
	 * @param string $key raw condition key.
	 * @return string actual key.
	 */
	protected function normalizeConditionKeyword($key)
	{
		static $map = [
			'or' => '$or',
			'>' => '$gt',
			'>=' => '$gte',
			'<' => '$lt',
			'<=' => '$lte',
			'!=' => '$ne',
			'<>' => '$ne',
			'in' => '$in',
			'not in' => '$nin',
			'all' => '$all',
			'size' => '$size',
			'type' => '$type',
			'exists' => '$exists',
			'notexists' => '$exists',
			'elemmatch' => '$elemMatch',
			'mod' => '$mod',
			'%' => '$mod',
			'=' => '$$eq',
			'==' => '$$eq',
			'where' => '$where'
		];
		$key = strtolower($key);
		if (array_key_exists($key, $map)) {
			return $map[$key];
		} else {
			return $key;
		}
	}

	/**
	 * Builds up Mongo condition from user friendly condition.
	 * @param array $condition raw condition.
	 * @return array normalized Mongo condition.
	 * @throws \yii\base\InvalidParamException on invalid condition given.
	 */
	public function buildCondition($condition)
	{
		if (!is_array($condition)) {
			throw new InvalidParamException('Condition should be an array.');
		}
		$result = [];
		foreach ($condition as $key => $value) {
			if (is_array($value)) {
				$actualValue = $this->buildCondition($value);
			} else {
				$actualValue = $value;
			}
			if (is_numeric($key)) {
				$result[] = $actualValue;
			} else {
				$key = $this->normalizeConditionKeyword($key);
				if (strncmp('$', $key, 1) !== 0 && array_key_exists(0, $actualValue)) {
					// shortcut for IN condition
					$result[$key]['$in'] = $actualValue;
				} else {
					$result[$key] = $actualValue;
				}
			}
		}
		return $result;
	}
}