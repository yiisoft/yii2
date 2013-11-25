<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;
use Guzzle\Http\Client;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveQueryTrait;
use yii\helpers\Json;

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
		$command = $this->createCommand($db);
		$result = $command->search();
		if (empty($result['hits'])) {
			return [];
		}
		$models = $this->createModels($result['hits']);
		if ($this->asArray) {
			foreach($models as $key => $model) {
				$models[$key] = $model['_source'];
				$models[$key][ActiveRecord::PRIMARY_KEY_NAME] = $model['_id'];
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
			$rows = $command->search()['hits'];
			$result = [];
			foreach ($rows as $row) {
				$result[] = $row['_id'];
			}
			return $result;
		}
		return parent::column($field, $db);
	}
}
