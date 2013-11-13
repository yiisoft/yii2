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
 * ActiveQuery instances are usually created by [[ActiveRecord::find()]], [[ActiveRecord::findBySql()]]
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
 * @since 2.0
 */
class ActiveQuery extends Query
{
	use \yii\ar\ActiveQueryTrait;

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
				$this->populateRelations($models, $this->with);
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
				/** @var $class ActiveRecord */
				$class = $this->modelClass;
				$model = $class::create($row);
			}
			if (!empty($this->with)) {
				$models = [$model];
				$this->populateRelations($models, $this->with);
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
		/** @var $modelClass ActiveRecord */
		$modelClass = $this->modelClass;
		if ($db === null) {
			$db = $modelClass::getDb();
		}

		$params = $this->params;
		if ($this->sql === null) {
			if ($this->from === null) {
				$tableName = $modelClass::tableName();
				if ($this->select === null && !empty($this->join)) {
					$this->select = ["$tableName.*"];
				}
				$this->from = [$tableName];
			}
			list ($this->sql, $params) = $db->getQueryBuilder()->build($this);
		}
		return $db->createCommand($this->sql, $params);
	}
}
