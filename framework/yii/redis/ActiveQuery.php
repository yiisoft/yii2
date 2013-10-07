<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\redis;

use yii\ar\BaseActiveQuery;

/**
 * ActiveQuery represents a query associated with an Active Record class.
 *
 * ActiveQuery instances are usually created by [[ActiveRecord::find()]]
 * and [[ActiveRecord::count()]].
 *
 * ActiveQuery mainly provides the following methods to retrieve the query results:
 *
 * - [[one()]]: returns a single record populated with the first row of data.
 * - [[all()]]: returns all records based on the query results.
 * - [[count()]]: returns the number of records.
 * - [[sum()]]: returns the sum over the specified column.
 * - [[average()]]: returns the average over the specified column.
 * - [[min()]]: returns the min over the specified column.
 * - [[max()]]: returns the max over the specified column.
 * - [[scalar()]]: returns the value of the first column in the first row of the query result.
 * - [[exists()]]: returns a value indicating whether the query result has data or not.
 *
 * You can use query methods, such as [[where()]], [[limit()]] and [[orderBy()]] to customize the query options.
 *
 * ActiveQuery also provides the following additional query options:
 *
 * - [[with()]]: list of relations that this query should be performed with.
 * - [[indexBy()]]: the name of the column by which the query result should be indexed.
 * - [[asArray()]]: whether to return each record as an array.
 *
 * These options can be configured using methods of the same name. For example:
 *
 * ~~~
 * $customers = Customer::find()->with('orders')->asArray()->all();
 * ~~~
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ActiveQuery extends Query
{
	use BaseActiveQuery;

	/**
	 * Executes query and returns all results as an array.
	 * @return array the query results. If the query results in nothing, an empty array will be returned.
	 */
	public function all()
	{
		// TODO add support for orderBy
		$data = $this->executeScript('All');
		$rows = array();
		foreach($data as $dataRow) {
			$row = array();
			$c = count($dataRow);
			for($i = 0; $i < $c; ) {
				$row[$dataRow[$i++]] = $dataRow[$i++];
			}
			$rows[] = $row;
		}
		if (!empty($rows)) {
			$models = $this->createModels($rows);
			if (!empty($this->with)) {
				$this->populateRelations($models, $this->with);
			}
			return $models;
		} else {
			return array();
		}
	}

	/**
	 * Executes query and returns a single row of result.
	 * @return ActiveRecord|array|null a single row of query result. Depending on the setting of [[asArray]],
	 * the query result may be either an array or an ActiveRecord object. Null will be returned
	 * if the query results in nothing.
	 */
	public function one()
	{
		// TODO add support for orderBy
		$data = $this->executeScript('One');
		if ($data === array()) {
			return null;
		}
		$row = array();
		$c = count($data);
		for($i = 0; $i < $c; ) {
			$row[$data[$i++]] = $data[$i++];
		}
		if ($this->asArray) {
			$model = $row;
		} else {
			/** @var $class ActiveRecord */
			$class = $this->modelClass;
			$model = $class::create($row);
		}
		if (!empty($this->with)) {
			$models = array($model);
			$this->populateRelations($models, $this->with);
			$model = $models[0];
		}
		return $model;
	}

	/**
	 * Executes the query and returns the first column of the result.
	 * @param string $column name of the column to select
	 * @return array the first column of the query result. An empty array is returned if the query results in nothing.
	 */
	public function column($column)
	{
		// TODO add support for indexBy and orderBy
		return $this->executeScript('Column', $column);
	}

	/**
	 * Returns the number of records.
	 * @return integer number of records
	 */
	public function count()
	{
		if ($this->offset === null && $this->limit === null && $this->where === null) {
			$modelClass = $this->modelClass;
			/** @var Connection $db */
			$db = $modelClass::getDb();
			return $db->executeCommand('LLEN', array($modelClass::tableName()));
		} else {
			return $this->executeScript('Count');
		}
	}

	/**
	 * Returns the number of records.
	 * @param string $column the column to sum up
	 * @return integer number of records
	 */
	public function sum($column)
	{
		return $this->executeScript('Sum', $column);
	}

	/**
	 * Returns the average of the specified column values.
	 * @param string $column the column name or expression.
	 * Make sure you properly quote column names in the expression.
	 * @return integer the average of the specified column values.
	 */
	public function average($column)
	{
		return $this->executeScript('Average', $column);
	}

	/**
	 * Returns the minimum of the specified column values.
	 * @param string $column the column name or expression.
	 * Make sure you properly quote column names in the expression.
	 * @return integer the minimum of the specified column values.
	 */
	public function min($column)
	{
		return $this->executeScript('Min', $column);
	}

	/**
	 * Returns the maximum of the specified column values.
	 * @param string $column the column name or expression.
	 * Make sure you properly quote column names in the expression.
	 * @return integer the maximum of the specified column values.
	 */
	public function max($column)
	{
		return $this->executeScript('Max', $column);
	}

	/**
	 * Returns the query result as a scalar value.
	 * The value returned will be the first column in the first row of the query results.
	 * @param string $column name of the column to select
	 * @return string|boolean the value of the first column in the first row of the query result.
	 * False is returned if the query result is empty.
	 */
	public function scalar($column)
	{
		$record = $this->one();
		return $record->$column;
	}

	/**
	 * Returns a value indicating whether the query result contains any row of data.
	 * @return boolean whether the query result contains any row of data.
	 */
	public function exists()
	{
		return $this->one() !== null;
	}

	/**
	 * Executes a script created by [[LuaScriptBuilder]]
	 * @param string $type
	 * @param null $column
	 * @return array|bool|null|string
	 */
	protected function executeScript($type, $columnName=null)
	{
		if (($data = $this->findByPk($type)) === false) {
			$modelClass = $this->modelClass;
			/** @var Connection $db */
			$db = $modelClass::getDb();

			$method = 'build' . $type;
			$script = $db->getLuaScriptBuilder()->$method($this, $columnName);
			return $db->executeCommand('EVAL', array($script, 0));
		}
		return $data;
	}

	/**
	 * Fetch by pk if possible as this is much faster
	 */
	private function findByPk($type, $columnName = null)
	{
		$modelClass = $this->modelClass;
		if (is_array($this->where) && !isset($this->where[0]) && $modelClass::isPrimaryKey(array_keys($this->where))) {
			/** @var Connection $db */
			$db = $modelClass::getDb();

			if (count($this->where) == 1) {
				$pks = (array) reset($this->where);
			} else {
				// TODO support IN for composite PK
				return false;
			}

			$start = $this->offset === null ? 0 : $this->offset;
			$i = 0;
			$data = array();
			foreach($pks as $pk) {
				if (++$i > $start && ($this->limit === null || $i <= $start + $this->limit)) {
					$key = $modelClass::tableName() . ':a:' . $modelClass::buildKey($pk);
					$result = $db->executeCommand('HGETALL', array($key));
					if (!empty($result)) {
						$data[] = $result;
						if ($type === 'One' && $this->orderBy === null) {
							break;
						}
					}
				}
			}
			// TODO support orderBy

			switch($type) {
				case 'All':
					return $data;
				case 'One':
					return reset($data);
				case 'Column':
					// TODO support indexBy
					$column = array();
					foreach($data as $dataRow) {
						$row = array();
						$c = count($dataRow);
						for($i = 0; $i < $c; ) {
							$row[$dataRow[$i++]] = $dataRow[$i++];
						}
						$column[] = $row[$columnName];
					}
					return $column;
				case 'Count':
					return count($data);
				case 'Sum':
					$sum = 0;
					foreach($data as $dataRow) {
						$c = count($dataRow);
						for($i = 0; $i < $c; ) {
							if ($dataRow[$i++] == $columnName) {
								$sum += $dataRow[$i];
								break;
							}
						}
					}
					return $sum;
				case 'Average':
					$sum = 0;
					$count = 0;
					foreach($data as $dataRow) {
						$count++;
						$c = count($dataRow);
						for($i = 0; $i < $c; ) {
							if ($dataRow[$i++] == $columnName) {
								$sum += $dataRow[$i];
								break;
							}
						}
					}
					return $sum / $count;
				case 'Min':
					$min = null;
					foreach($data as $dataRow) {
						$c = count($dataRow);
						for($i = 0; $i < $c; ) {
							if ($dataRow[$i++] == $columnName && ($min == null || $dataRow[$i] < $min)) {
								$min = $dataRow[$i];
								break;
							}
						}
					}
					return $min;
				case 'Max':
					$max = null;
					foreach($data as $dataRow) {
						$c = count($dataRow);
						for($i = 0; $i < $c; ) {
							if ($dataRow[$i++] == $columnName && ($max == null || $dataRow[$i] > $max)) {
								$max = $dataRow[$i];
								break;
							}
						}
					}
					return $max;
			}
		}
		return false;
	}
}
