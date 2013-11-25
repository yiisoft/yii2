<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

use yii\db\ActiveQueryInterface;
use yii\db\ActiveQueryTrait;

/**
 * ActiveQuery represents a [[Query]] associated with an [[ActiveRecord]] class.
 *
 * ActiveQuery instances are usually created by [[ActiveRecord::find()]].
 *
 * ActiveQuery mainly provides the following methods to retrieve the query results:
 *
 * - [[one()]]: returns a single record populated with the first row of data.
 * - [[all()]]: returns all records based on the query results.
 * - [[count()]]: returns the number of records.
 * - [[scalar()]]: returns the value of the first column in the first row of the query result.
 * - [[column()]]: returns the value of the first column in the query result.
 * - [[exists()]]: returns a value indicating whether the query result has data or not.
 *
 * Because ActiveQuery extends from [[Query]], one can use query methods, such as [[where()]],
 * [[orderBy()]] to customize the query options.
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
class ActiveQuery extends Query implements ActiveQueryInterface
{
	use ActiveQueryTrait;

	/**
	 * Creates a DB command that can be used to execute this query.
	 * @param Connection $db the DB connection used to create the DB command.
	 * If null, the DB connection returned by [[modelClass]] will be used.
	 * @return Command the created DB command instance.
	 */
	public function createCommand($db = null)
	{
		/** @var ActiveRecord $modelClass */
		$modelClass = $this->modelClass;
		if ($db === null) {
			$db = $modelClass::getDb();
		}

		if ($this->type === null) {
			$this->type = $modelClass::type();
		}
		if ($this->index === null) {
			$this->index = $modelClass::index();
			$this->type = $modelClass::type();
		}
		$commandConfig = $db->getQueryBuilder()->build($this);
		return $db->createCommand($commandConfig);
	}

	/**
	 * Executes query and returns all results as an array.
	 * @param Connection $db the DB connection used to create the DB command.
	 * If null, the DB connection returned by [[modelClass]] will be used.
	 * @return array the query results. If the query results in nothing, an empty array will be returned.
	 */
	public function all($db = null)
	{
		$result = $this->createCommand($db)->search();
		if (empty($result['hits']['hits'])) {
			return [];
		}
		if ($this->fields !== null) {
			foreach ($result['hits']['hits'] as &$row) {
				$row['_source'] = isset($row['fields']) ? $row['fields'] : [];
				unset($row['fields']);
			}
			unset($row);
		}
		if ($this->asArray && $this->indexBy) {
			foreach ($result['hits']['hits'] as &$row) {
				$row['_source'][ActiveRecord::PRIMARY_KEY_NAME] = $row['_id'];
				$row = $row['_source'];
			}
		}
		$models = $this->createModels($result['hits']['hits']);
		if ($this->asArray && !$this->indexBy) {
			foreach($models as $key => $model) {
				$model['_source'][ActiveRecord::PRIMARY_KEY_NAME] = $model['_id'];
				$models[$key] = $model['_source'];
			}
		}
		if (!empty($this->with)) {
			$this->findWith($this->with, $models);
		}
		return $models;
	}

	/**
	 * Executes query and returns a single row of result.
	 * @param Connection $db the DB connection used to create the DB command.
	 * If null, the DB connection returned by [[modelClass]] will be used.
	 * @return ActiveRecord|array|null a single row of query result. Depending on the setting of [[asArray]],
	 * the query result may be either an array or an ActiveRecord object. Null will be returned
	 * if the query results in nothing.
	 */
	public function one($db = null)
	{
		if (($result = parent::one($db)) === false) {
			return null;
		}
		if ($this->asArray) {
			$model = $result['_source'];
			$model[ActiveRecord::PRIMARY_KEY_NAME] = $result['_id'];
		} else {
			/** @var ActiveRecord $class */
			$class = $this->modelClass;
			$model = $class::create($result);
		}
		if (!empty($this->with)) {
			$models = [$model];
			$this->findWith($this->with, $models);
			$model = $models[0];
		}
		return $model;
	}

	/**
	 * @inheritDocs
	 */
	public function search($db = null, $options = [])
	{
		$result = $this->createCommand($db)->search($options);
		if (!empty($result['hits']['hits'])) {
			$models = $this->createModels($result['hits']['hits']);
			if ($this->asArray) {
				foreach($models as $key => $model) {
					$model['_source'][ActiveRecord::PRIMARY_KEY_NAME] = $model['_id'];
					$models[$key] = $model['_source'];
				}
			}
			if (!empty($this->with)) {
				$this->findWith($this->with, $models);
			}
			$result['hits']['hits'] = $models;
		}
		return $result;
	}

	/**
	 * @inheritDocs
	 */
	public function scalar($field, $db = null)
	{
		$record = parent::one($db);
		if ($record !== false) {
			if ($field == ActiveRecord::PRIMARY_KEY_NAME) {
				return $record['_id'];
			} elseif (isset($record['_source'][$field])) {
				return $record['_source'][$field];
			}
		}
		return null;
	}

	/**
	 * @inheritDocs
	 */
	public function column($field, $db = null)
	{
		if ($field == ActiveRecord::PRIMARY_KEY_NAME) {
			$command = $this->createCommand($db);
			$command->queryParts['fields'] = [];
			$result = $command->search();
			if (empty($result['hits']['hits'])) {
				return [];
			}
			$column = [];
			foreach ($result['hits']['hits'] as $row) {
				$column[] = $row['_id'];
			}
			return $column;
		}
		return parent::column($field, $db);
	}
}
