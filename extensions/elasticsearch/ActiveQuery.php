<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

use yii\db\ActiveQueryInterface;
use yii\db\ActiveQueryTrait;
use yii\db\ActiveRelationInterface;
use yii\db\ActiveRelationTrait;

/**
 * ActiveQuery represents a [[Query]] associated with an [[ActiveRecord]] class.
 *
 * An ActiveQuery can be a normal query or be used in a relational context.
 *
 * ActiveQuery instances are usually created by [[ActiveRecord::find()]].
 * Relational queries are created by [[ActiveRecord::hasOne()]] and [[ActiveRecord::hasMany()]].
 *
 * Normal Query
 * ------------
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
 * ```php
 * $customers = Customer::find()->with('orders')->asArray()->all();
 * ```
 * > NOTE: elasticsearch limits the number of records returned to 10 records by default.
 * > If you expect to get more records you should specify limit explicitly.
 *
 * Relational query
 * ----------------
 *
 * In relational context ActiveQuery represents a relation between two Active Record classes.
 *
 * Relational ActiveQuery instances are usually created by calling [[ActiveRecord::hasOne()]] and
 * [[ActiveRecord::hasMany()]]. An Active Record class declares a relation by defining
 * a getter method which calls one of the above methods and returns the created ActiveQuery object.
 *
 * A relation is specified by [[link]] which represents the association between columns
 * of different tables; and the multiplicity of the relation is indicated by [[multiple]].
 *
 * If a relation involves a pivot table, it may be specified by [[via()]].
 * This methods may only be called in a relational context. Same is true for [[inverseOf()]], which
 * marks a relation as inverse of another relation.
 *
 * > NOTE: elasticsearch limits the number of records returned by any query to 10 records by default.
 * > If you expect to get more records you should specify limit explicitly in relation definition.
 * > This is also important for relations that use [[via()]] so that if via records are limited to 10
 * > the relations records can also not be more than 10.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ActiveQuery extends Query implements ActiveQueryInterface, ActiveRelationInterface
{
	use ActiveQueryTrait;
	use ActiveRelationTrait;

	/**
	 * Creates a DB command that can be used to execute this query.
	 * @param Connection $db the DB connection used to create the DB command.
	 * If null, the DB connection returned by [[modelClass]] will be used.
	 * @return Command the created DB command instance.
	 */
	public function createCommand($db = null)
	{
		if ($this->primaryModel !== null) {
			// lazy loading
			if (is_array($this->via)) {
				// via relation
				/** @var ActiveQuery $viaQuery */
				list($viaName, $viaQuery) = $this->via;
				if ($viaQuery->multiple) {
					$viaModels = $viaQuery->all();
					$this->primaryModel->populateRelation($viaName, $viaModels);
				} else {
					$model = $viaQuery->one();
					$this->primaryModel->populateRelation($viaName, $model);
					$viaModels = $model === null ? [] : [$model];
				}
				$this->filterByModels($viaModels);
			} else {
				$this->filterByModels([$this->primaryModel]);
			}
		}

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
		/** @var ActiveRecord $modelClass */
		$modelClass = $this->modelClass;
		$pk = $modelClass::primaryKey()[0];
		if ($this->asArray && $this->indexBy) {
			foreach ($result['hits']['hits'] as &$row) {
				if ($pk === '_id') {
					$row['_source']['_id'] = $row['_id'];
				}
				$row['_source']['_score'] = $row['_score'];
				$row = $row['_source'];
			}
			unset($row);
		}
		$models = $this->createModels($result['hits']['hits']);
		if ($this->asArray && !$this->indexBy) {
			foreach($models as $key => $model) {
				if ($pk === '_id') {
					$model['_source']['_id'] = $model['_id'];
				}
				$model['_source']['_score'] = $model['_score'];
				$models[$key] = $model['_source'];
			}
		}
		if (!empty($this->with)) {
			$this->findWith($this->with, $models);
		}
		if (!$this->asArray) {
			foreach($models as $model) {
				$model->afterFind();
			}
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
			/** @var ActiveRecord $modelClass */
			$modelClass = $this->modelClass;
			$model = $result['_source'];
			$pk = $modelClass::primaryKey()[0];
			if ($pk === '_id') {
				$model['_id'] = $result['_id'];
			}
			$model['_score'] = $result['_score'];
		} else {
			/** @var ActiveRecord $class */
			$class = $this->modelClass;
			$model = $class::instantiate($result);
			$class::populateRecord($model, $result);
		}
		if (!empty($this->with)) {
			$models = [$model];
			$this->findWith($this->with, $models);
			$model = $models[0];
		}
		if (!$this->asArray) {
			$model->afterFind();
		}
		return $model;
	}

	/**
	 * @inheritdoc
	 */
	public function search($db = null, $options = [])
	{
		$result = $this->createCommand($db)->search($options);
		if (!empty($result['hits']['hits'])) {
			$models = $this->createModels($result['hits']['hits']);
			if ($this->asArray) {
				/** @var ActiveRecord $modelClass */
				$modelClass = $this->modelClass;
				$pk = $modelClass::primaryKey()[0];
				foreach($models as $key => $model) {
					if ($pk === '_id') {
						$model['_source']['_id'] = $model['_id'];
					}
					$model['_source']['_score'] = $model['_score'];
					$models[$key] = $model['_source'];
				}
			}
			if (!empty($this->with)) {
				$this->findWith($this->with, $models);
			}
			if (!$this->asArray) {
				foreach($models as $model) {
					$model->afterFind();
				}
			}
			$result['hits']['hits'] = $models;
		}
		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function scalar($field, $db = null)
	{
		$record = parent::one($db);
		if ($record !== false) {
			if ($field == '_id') {
				return $record['_id'];
			} elseif (isset($record['_source'][$field])) {
				return $record['_source'][$field];
			}
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function column($field, $db = null)
	{
		if ($field == '_id') {
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
