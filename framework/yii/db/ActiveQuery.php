<?php
/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * ActiveQuery represents a DB query associated with an Active Record class.
 *
 * ActiveQuery instances are usually created by [[ActiveRecord::find()]] and [[ActiveRecord::findBySql()]].
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
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ActiveQuery extends Query implements ActiveQueryInterface
{
	use ActiveQueryTrait;

	/**
	 * @var string the SQL statement to be executed for retrieving AR records.
	 * This is set by [[ActiveRecord::findBySql()]].
	 */
	public $sql;


	/**
	 * Executes query and returns all results as an array.
	 * @param Connection $db the DB connection used to create the DB command.
	 * If null, the DB connection returned by [[modelClass]] will be used.
	 * @return array the query results. If the query results in nothing, an empty array will be returned.
	 */
	public function all($db = null)
	{
		$command = $this->createCommand($db);
		$rows = $command->queryAll();
		if (!empty($rows)) {
			$models = $this->createModels($rows);
			if (!empty($this->with)) {
				$this->findWith($this->with, $models);
			}
			return $models;
		} else {
			return [];
		}
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
		$command = $this->createCommand($db);
		$row = $command->queryOne();
		if ($row !== false) {
			if ($this->asArray) {
				$model = $row;
			} else {
				/** @var ActiveRecord $class */
				$class = $this->modelClass;
				$model = $class::create($row);
			}
			if (!empty($this->with)) {
				$models = [$model];
				$this->findWith($this->with, $models);
				$model = $models[0];
			}
			return $model;
		} else {
			return null;
		}
	}

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

		if ($this->sql === null) {
			$select = $this->select;
			$from = $this->from;

			if ($this->from === null) {
				$tableName = $modelClass::tableName();
				if ($this->select === null && !empty($this->join)) {
					$this->select = ["$tableName.*"];
				}
				$this->from = [$tableName];
			}
			list ($sql, $params) = $db->getQueryBuilder()->build($this);

			$this->select = $select;
			$this->from = $from;
		} else {
			$sql = $this->sql;
			$params = $this->params;
		}
		return $db->createCommand($sql, $params);
	}

	public function joinWith($with, $eagerLoading = true, $joinType = 'INNER JOIN')
	{
		$with = (array)$with;
		$this->joinWithRelations(new $this->modelClass, $with, $joinType);

		if (is_array($eagerLoading)) {
			foreach ($with as $name => $callback) {
				if (is_integer($name)) {
					if (!in_array($callback, $eagerLoading, true)) {
						unset($with[$name]);
					}
				} elseif (!in_array($name, $eagerLoading, true)) {
					unset($with[$name]);
				}
			}
			$this->with($with);
		} elseif ($eagerLoading) {
			$this->with($with);
		}
		return $this;
	}

	/**
	 * @param ActiveRecord $model
	 * @param array $with
	 * @param string|array $joinType
	 */
	private function joinWithRelations($model, $with, $joinType)
	{
		$relations = [];

		foreach ($with as $name => $callback) {
			if (is_integer($name)) {
				$name = $callback;
				$callback = null;
			}

			$primaryModel = $model;
			$parent = $this;
			$prefix = '';
			while (($pos = strpos($name, '.')) !== false) {
				$childName = substr($name, $pos + 1);
				$name = substr($name, 0, $pos);
				$fullName = $prefix === '' ? $name : "$prefix.$name";
				if (!isset($relations[$fullName])) {
					$relations[$fullName] = $relation = $primaryModel->getRelation($name);
					$this->joinWithRelation($parent, $relation, $this->getJoinType($joinType, $fullName));
				} else {
					$relation = $relations[$fullName];
				}
				$primaryModel = new $relation->modelClass;
				$parent = $relation;
				$prefix = $fullName;
				$name = $childName;
			}

			$fullName = $prefix === '' ? $name : "$prefix.$name";
			if (!isset($relations[$fullName])) {
				$relations[$fullName] = $relation = $primaryModel->getRelation($name);
				if ($callback !== null) {
					call_user_func($callback, $relation);
				}
				$this->joinWithRelation($parent, $relation, $this->getJoinType($joinType, $fullName));
			}
		}
	}

	private function getJoinType($joinType, $name)
	{
		if (is_array($joinType) && isset($joinType[$name])) {
			return $joinType[$name];
		} else {
			return is_string($joinType) ? $joinType : 'INNER JOIN';
		}
	}

	/**
	 * @param ActiveQuery $query
	 * @return string
	 */
	private function getQueryTableName($query)
	{
		if (empty($query->from)) {
			/** @var ActiveRecord $modelClass */
			$modelClass = $query->modelClass;
			return $modelClass::tableName();
		} else {
			return reset($query->from);
		}
	}

	/**
	 * @param ActiveQuery $parent
	 * @param ActiveRelation $child
	 * @param string $joinType
	 */
	private function joinWithRelation($parent, $child, $joinType)
	{
		$parentTable = $this->getQueryTableName($parent);
		$childTable = $this->getQueryTableName($child);
		if (!empty($child->link)) {
			$on = [];
			foreach ($child->link as $childColumn => $parentColumn) {
				$on[] = '{{' . $parentTable . "}}.[[$parentColumn]] = {{" . $childTable . "}}.[[$childColumn]]";
			}
			$on = implode(' AND ', $on);
		} else {
			$on = '';
		}
		$this->join($joinType, $childTable, $on);
		if (!empty($child->where)) {
			$this->andWhere($child->where);
		}
		if (!empty($child->having)) {
			$this->andHaving($child->having);
		}
		if (!empty($child->orderBy)) {
			$this->addOrderBy($child->orderBy);
		}
		if (!empty($child->groupBy)) {
			$this->addGroupBy($child->groupBy);
		}
		if (!empty($child->params)) {
			$this->addParams($child->params);
		}
		if (!empty($child->join)) {
			foreach ($child->join as $join) {
				$this->join[] = $join;
			}
		}
		if (!empty($child->union)) {
			foreach ($child->union as $union) {
				$this->union[] = $union;
			}
		}
	}
}
