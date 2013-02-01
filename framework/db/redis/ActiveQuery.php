<?php
/**
 * ActiveRecord class file.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\redis;

/**
 * ActiveRecord is the base class for classes representing relational data in terms of objects.
 *
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ActiveQuery extends \yii\db\ActiveQuery
{
	/**
	 * Executes query and returns all results as an array.
	 * @return array the query results. If the query results in nothing, an empty array will be returned.
	 */
	public function all()
	{
		$command = $this->createCommand();
		$rows = $command->queryAll();
		if ($rows !== array()) {
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
		$command = $this->createCommand();
		$row = $command->queryRow();
		if ($row !== false && !$this->asArray) {
			/** @var $class ActiveRecord */
			$class = $this->modelClass;
			$model = $class::create($row);
			if (!empty($this->with)) {
				$models = array($model);
				$this->populateRelations($models, $this->with);
				$model = $models[0];
			}
			return $model;
		} else {
			return $row === false ? null : $row;
		}
	}

	/**
	 * Returns the number of records.
	 * @param string $q the COUNT expression. Defaults to '*'.
	 * Make sure you properly quote column names.
	 * @return integer number of records
	 */
	public function count($q = '*')
	{
		$this->select = array("COUNT($q)");
		return $this->createCommand()->queryScalar();
	}

	/**
	 * Returns the sum of the specified column values.
	 * @param string $q the column name or expression.
	 * Make sure you properly quote column names.
	 * @return integer the sum of the specified column values
	 */
	public function sum($q)
	{
		$this->select = array("SUM($q)");
		return $this->createCommand()->queryScalar();
	}

	/**
	 * Returns the average of the specified column values.
	 * @param string $q the column name or expression.
	 * Make sure you properly quote column names.
	 * @return integer the average of the specified column values.
	 */
	public function average($q)
	{
		$this->select = array("AVG($q)");
		return $this->createCommand()->queryScalar();
	}

	/**
	 * Returns the minimum of the specified column values.
	 * @param string $q the column name or expression.
	 * Make sure you properly quote column names.
	 * @return integer the minimum of the specified column values.
	 */
	public function min($q)
	{
		$this->select = array("MIN($q)");
		return $this->createCommand()->queryScalar();
	}

	/**
	 * Returns the maximum of the specified column values.
	 * @param string $q the column name or expression.
	 * Make sure you properly quote column names.
	 * @return integer the maximum of the specified column values.
	 */
	public function max($q)
	{
		$this->select = array("MAX($q)");
		return $this->createCommand()->queryScalar();
	}

	/**
	 * Returns the query result as a scalar value.
	 * The value returned will be the first column in the first row of the query results.
	 * @return string|boolean the value of the first column in the first row of the query result.
	 * False is returned if the query result is empty.
	 */
	public function scalar()
	{
		return $this->createCommand()->queryScalar();
	}

	/**
	 * Returns a value indicating whether the query result contains any row of data.
	 * @return boolean whether the query result contains any row of data.
	 */
	public function exists()
	{
		$this->select = array(new Expression('1'));
		return $this->scalar() !== false;
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
		if ($this->sql === null) {
			if ($this->from === null) {
				$tableName = $modelClass::tableName();
				$this->from = array($tableName);
			}
			/** @var $qb QueryBuilder */
			$qb = $db->getQueryBuilder();
			$this->sql = $qb->build($this);
		}
		return $db->createCommand($this->sql, $this->params);
	}

}
