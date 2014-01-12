<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;
use yii\base\InvalidCallException;

/**
 * ActiveQueryTrait implements the common methods and properties for active record query classes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
trait ActiveQueryTrait
{
	/**
	 * @var string the name of the ActiveRecord class.
	 */
	public $modelClass;
	/**
	 * @var array a list of relations that this query should be performed with
	 */
	public $with;
	/**
	 * @var boolean whether to return each record as an array. If false (default), an object
	 * of [[modelClass]] will be created to represent each record.
	 */
	public $asArray;


	/**
	 * PHP magic method.
	 * This method allows calling static method defined in [[modelClass]] via this query object.
	 * It is mainly implemented for supporting the feature of scope.
	 *
	 * @param string $name the method name to be called
	 * @param array $params the parameters passed to the method
	 * @throws \yii\base\InvalidCallException
	 * @return mixed the method return result
	 */
	public function __call($name, $params)
	{
		if (method_exists($this->modelClass, $name)) {
			$method = new \ReflectionMethod($this->modelClass, $name);
			if (!$method->isStatic() || !$method->isPublic()) {
				throw new InvalidCallException("The scope method \"{$this->modelClass}::$name()\" must be public and static.");
			}
			array_unshift($params, $this);
			call_user_func_array([$this->modelClass, $name], $params);
			return $this;
		} else {
			return parent::__call($name, $params);
		}
	}

	/**
	 * Sets the [[asArray]] property.
	 * @param boolean $value whether to return the query results in terms of arrays instead of Active Records.
	 * @return static the query object itself
	 */
	public function asArray($value = true)
	{
		$this->asArray = $value;
		return $this;
	}

	/**
	 * Specifies the relations with which this query should be performed.
	 *
	 * The parameters to this method can be either one or multiple strings, or a single array
	 * of relation names and the optional callbacks to customize the relations.
	 *
	 * A relation name can refer to a relation defined in [[modelClass]]
	 * or a sub-relation that stands for a relation of a related record.
	 * For example, `orders.address` means the `address` relation defined
	 * in the model class corresponding to the `orders` relation.
	 *
	 * The followings are some usage examples:
	 *
	 * ~~~
	 * // find customers together with their orders and country
	 * Customer::find()->with('orders', 'country')->all();
	 * // find customers together with their orders and the orders' shipping address
	 * Customer::find()->with('orders.address')->all();
	 * // find customers together with their country and orders of status 1
	 * Customer::find()->with([
	 *     'orders' => function($query) {
	 *         $query->andWhere('status = 1');
	 *     },
	 *     'country',
	 * ])->all();
	 * ~~~
	 *
	 * You can call `with()` multiple times. Each call will add relations to the existing ones.
	 * For example, the following two statements are equivalent:
	 *
	 * ~~~
	 * Customer::find()->with('orders', 'country')->all();
	 * Customer::find()->with('orders')->with('country')->all();
	 * ~~~
	 *
	 * @return static the query object itself
	 */
	public function with()
	{
		$with = func_get_args();
		if (isset($with[0]) && is_array($with[0])) {
			// the parameter is given as an array
			$with = $with[0];
		}

		if (empty($this->with)) {
			$this->with = $with;
		} elseif (!empty($with)) {
			foreach ($with as $name => $value) {
				if (is_integer($name)) {
					// repeating relation is fine as normalizeRelations() handle it well
					$this->with[] = $value;
				} else {
					$this->with[$name] = $value;
				}
			}
		}

		return $this;
	}

	/**
	 * Converts found rows into model instances
	 * @param array $rows
	 * @return array|ActiveRecord[]
	 */
	private function createModels($rows)
	{
		$models = [];
		if ($this->asArray) {
			if ($this->indexBy === null) {
				return $rows;
			}
			foreach ($rows as $row) {
				if (is_string($this->indexBy)) {
					$key = $row[$this->indexBy];
				} else {
					$key = call_user_func($this->indexBy, $row);
				}
				$models[$key] = $row;
			}
		} else {
			/** @var ActiveRecord $class */
			$class = $this->modelClass;
			if ($this->indexBy === null) {
				foreach ($rows as $row) {
					$models[] = $class::create($row);
				}
			} else {
				foreach ($rows as $row) {
					$model = $class::create($row);
					if (is_string($this->indexBy)) {
						$key = $model->{$this->indexBy};
					} else {
						$key = call_user_func($this->indexBy, $model);
					}
					$models[$key] = $model;
				}
			}
		}
		return $models;
	}

	/**
	 * Finds records corresponding to one or multiple relations and populates them into the primary models.
	 * @param array $with a list of relations that this query should be performed with. Please
	 * refer to [[with()]] for details about specifying this parameter.
	 * @param array $models the primary models (can be either AR instances or arrays)
	 */
	public function findWith($with, &$models)
	{
		$primaryModel = new $this->modelClass;
		$relations = $this->normalizeRelations($primaryModel, $with);
		foreach ($relations as $name => $relation) {
			if ($relation->asArray === null) {
				// inherit asArray from primary query
				$relation->asArray = $this->asArray;
			}
			$relation->populateRelation($name, $models);
		}
	}

	/**
	 * @param ActiveRecord $model
	 * @param array $with
	 * @return ActiveRelationInterface[]
	 */
	private function normalizeRelations($model, $with)
	{
		$relations = [];
		foreach ($with as $name => $callback) {
			if (is_integer($name)) {
				$name = $callback;
				$callback = null;
			}
			if (($pos = strpos($name, '.')) !== false) {
				// with sub-relations
				$childName = substr($name, $pos + 1);
				$name = substr($name, 0, $pos);
			} else {
				$childName = null;
			}

			if (!isset($relations[$name])) {
				$relation = $model->getRelation($name);
				$relation->primaryModel = null;
				$relations[$name] = $relation;
			} else {
				$relation = $relations[$name];
			}

			if (isset($childName)) {
				$relation->with[$childName] = $callback;
			} elseif ($callback !== null) {
				call_user_func($callback, $relation);
			}
		}
		return $relations;
	}
}
