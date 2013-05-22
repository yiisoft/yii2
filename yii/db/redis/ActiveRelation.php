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

use yii\base\NotSupportedException;

/**
 * ActiveRecord is the base class for classes representing relational data in terms of objects.
 *
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ActiveRelation extends \yii\db\redis\ActiveQuery
{
	/**
	 * @var boolean whether this relation should populate all query results into AR instances.
	 * If false, only the first row of the results will be retrieved.
	 */
	public $multiple;
	/**
	 * @var ActiveRecord the primary model that this relation is associated with.
	 * This is used only in lazy loading with dynamic query options.
	 */
	public $primaryModel;
	/**
	 * @var array the columns of the primary and foreign tables that establish the relation.
	 * The array keys must be columns of the table for this relation, and the array values
	 * must be the corresponding columns from the primary table.
	 * Do not prefix or quote the column names as this will be done automatically by Yii.
	 */
	public $link;
	/**
	 * @var array|ActiveRelation the query associated with the pivot table. Please call [[via()]]
	 * or [[viaTable()]] to set this property instead of directly setting it.
	 */
	public $via;

	/**
	 * Specifies the relation associated with the pivot table.
	 * @param string $relationName the relation name. This refers to a relation declared in [[primaryModel]].
	 * @param callable $callable a PHP callback for customizing the relation associated with the pivot table.
	 * Its signature should be `function($query)`, where `$query` is the query to be customized.
	 * @return ActiveRelation the relation object itself.
	 */
	public function via($relationName, $callable = null)
	{
		$relation = $this->primaryModel->getRelation($relationName);
		$this->via = array($relationName, $relation);
		if ($callable !== null) {
			call_user_func($callable, $relation);
		}
		return $this;
	}

	/**
	 * Specifies the pivot table.
	 * @param string $tableName the name of the pivot table.
	 * @param array $link the link between the pivot table and the table associated with [[primaryModel]].
	 * The keys of the array represent the columns in the pivot table, and the values represent the columns
	 * in the [[primaryModel]] table.
	 * @param callable $callable a PHP callback for customizing the relation associated with the pivot table.
	 * Its signature should be `function($query)`, where `$query` is the query to be customized.
	 * @return ActiveRelation
	 * /
	public function viaTable($tableName, $link, $callable = null)
	{
		$relation = new ActiveRelation(array(
			'modelClass' => get_class($this->primaryModel),
			'from' => array($tableName),
			'link' => $link,
			'multiple' => true,
			'asArray' => true,
		));
		$this->via = $relation;
		if ($callable !== null) {
			call_user_func($callable, $relation);
		}
		return $this;
	}*/

	/**
	 * Finds the related records and populates them into the primary models.
	 * This method is internally by [[ActiveQuery]]. Do not call it directly.
	 * @param string $name the relation name
	 * @param array $primaryModels primary models
	 * @return array the related models
	 * @throws InvalidConfigException
	 */
	public function findWith($name, &$primaryModels)
	{
		if (!is_array($this->link)) {
			throw new InvalidConfigException('Invalid link: it must be an array of key-value pairs.');
		}

		if ($this->via instanceof self) {
			// TODO
			// via pivot table
			/** @var $viaQuery ActiveRelation */
			$viaQuery = $this->via;
			$viaModels = $viaQuery->findPivotRows($primaryModels);
			$this->filterByModels($viaModels);
		} elseif (is_array($this->via)) {
			// TODO
			// via relation
			/** @var $viaQuery ActiveRelation */
			list($viaName, $viaQuery) = $this->via;
			$viaQuery->primaryModel = null;
			$viaModels = $viaQuery->findWith($viaName, $primaryModels);
			$this->filterByModels($viaModels);
		} else {
			$this->filterByModels($primaryModels);
		}

		if (count($primaryModels) === 1 && !$this->multiple) {
			$model = $this->one();
			foreach ($primaryModels as $i => $primaryModel) {
				if ($primaryModel instanceof ActiveRecord) {
					$primaryModel->populateRelation($name, $model);
				} else {
					$primaryModels[$i][$name] = $model;
				}
			}
			return array($model);
		} else {
			$models = $this->all();
			if (isset($viaModels, $viaQuery)) {
				$buckets = $this->buildBuckets($models, $this->link, $viaModels, $viaQuery->link);
			} else {
				$buckets = $this->buildBuckets($models, $this->link);
			}

			$link = array_values(isset($viaQuery) ? $viaQuery->link : $this->link);
			foreach ($primaryModels as $i => $primaryModel) {
				$key = $this->getModelKey($primaryModel, $link);
				$value = isset($buckets[$key]) ? $buckets[$key] : ($this->multiple ? array() : null);
				if ($primaryModel instanceof ActiveRecord) {
					$primaryModel->populateRelation($name, $value);
				} else {
					$primaryModels[$i][$name] = $value;
				}
			}
			return $models;
		}
	}

	/**
	 * @param array $models
	 * @param array $link
	 * @param array $viaModels
	 * @param array $viaLink
	 * @return array
	 */
	private function buildBuckets($models, $link, $viaModels = null, $viaLink = null)
	{
		$buckets = array();
		$linkKeys = array_keys($link);
		foreach ($models as $i => $model) {
			$key = $this->getModelKey($model, $linkKeys);
			if ($this->indexBy !== null) {
				$buckets[$key][$i] = $model;
			} else {
				$buckets[$key][] = $model;
			}
		}

		if ($viaModels !== null) {
			$viaBuckets = array();
			$viaLinkKeys = array_keys($viaLink);
			$linkValues = array_values($link);
			foreach ($viaModels as $viaModel) {
				$key1 = $this->getModelKey($viaModel, $viaLinkKeys);
				$key2 = $this->getModelKey($viaModel, $linkValues);
				if (isset($buckets[$key2])) {
					foreach ($buckets[$key2] as $i => $bucket) {
						if ($this->indexBy !== null) {
							$viaBuckets[$key1][$i] = $bucket;
						} else {
							$viaBuckets[$key1][] = $bucket;
						}
					}
				}
			}
			$buckets = $viaBuckets;
		}

		if (!$this->multiple) {
			foreach ($buckets as $i => $bucket) {
				$buckets[$i] = reset($bucket);
			}
		}
		return $buckets;
	}

	/**
	 * @param ActiveRecord|array $model
	 * @param array $attributes
	 * @return string
	 */
	private function getModelKey($model, $attributes)
	{
		if (count($attributes) > 1) {
			$key = array();
			foreach ($attributes as $attribute) {
				$key[] = $model[$attribute];
			}
			return serialize($key);
		} else {
			$attribute = reset($attributes);
			return $model[$attribute];
		}
	}


	/**
	 * @param array $models
	 */
	private function filterByModels($models)
	{
		$attributes = array_keys($this->link);
		$values = array();
		if (count($attributes) ===1) {
			// single key
			$attribute = reset($this->link);
			foreach ($models as $model) {
				$values[] = $model[$attribute];
			}
		} else {
			// composite keys
			foreach ($models as $model) {
				$v = array();
				foreach ($this->link as $attribute => $link) {
					$v[$attribute] = $model[$link];
				}
				$values[] = $v;
			}
		}
		$this->primaryKeys($values);
	}

}
