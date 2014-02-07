<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\InvalidConfigException;

/**
 * ActiveRelationTrait implements the common methods and properties for active record relation classes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
trait ActiveRelationTrait
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
	 * @var array the query associated with the pivot table. Please call [[via()]]
	 * to set this property instead of directly setting it.
	 */
	public $via;

	/**
	 * Clones internal objects.
	 */
	public function __clone()
	{
		// make a clone of "via" object so that the same query object can be reused multiple times
		if (is_object($this->via)) {
			$this->via = clone $this->via;
		} elseif (is_array($this->via)) {
			$this->via = [$this->via[0], clone $this->via[1]];
		}
	}

	/**
	 * Specifies the relation associated with the pivot table.
	 * @param string $relationName the relation name. This refers to a relation declared in [[primaryModel]].
	 * @param callable $callable a PHP callback for customizing the relation associated with the pivot table.
	 * Its signature should be `function($query)`, where `$query` is the query to be customized.
	 * @return static the relation object itself.
	 */
	public function via($relationName, $callable = null)
	{
		$relation = $this->primaryModel->getRelation($relationName);
		$this->via = [$relationName, $relation];
		if ($callable !== null) {
			call_user_func($callable, $relation);
		}
		return $this;
	}

	/**
	 * Finds the related records and populates them into the primary models.
	 * @param string $name the relation name
	 * @param array $primaryModels primary models
	 * @return array the related models
	 * @throws InvalidConfigException if [[link]] is invalid
	 */
	public function populateRelation($name, &$primaryModels)
	{
		if (!is_array($this->link)) {
			throw new InvalidConfigException('Invalid link: it must be an array of key-value pairs.');
		}

		if ($this->via instanceof self) {
			// via pivot table
			/** @var ActiveRelationTrait $viaQuery */
			$viaQuery = $this->via;
			$viaModels = $viaQuery->findPivotRows($primaryModels);
			$this->filterByModels($viaModels);
		} elseif (is_array($this->via)) {
			// via relation
			/** @var ActiveRelationTrait $viaQuery */
			list($viaName, $viaQuery) = $this->via;
			$viaQuery->primaryModel = null;
			$viaModels = $viaQuery->populateRelation($viaName, $primaryModels);
			$this->filterByModels($viaModels);
		} else {
			$this->filterByModels($primaryModels);
		}

		if (count($primaryModels) === 1 && !$this->multiple) {
			$model = $this->one();
			foreach ($primaryModels as $i => $primaryModel) {
				if ($primaryModel instanceof ActiveRecordInterface) {
					$primaryModel->populateRelation($name, $model);
				} else {
					$primaryModels[$i][$name] = $model;
				}
			}
			return [$model];
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
				$value = isset($buckets[$key]) ? $buckets[$key] : ($this->multiple ? [] : null);
				if ($primaryModel instanceof ActiveRecordInterface) {
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
		if ($viaModels !== null) {
			$map = [];
			$viaLinkKeys = array_keys($viaLink);
			$linkValues = array_values($link);
			foreach ($viaModels as $viaModel) {
				$key1 = $this->getModelKey($viaModel, $viaLinkKeys);
				$key2 = $this->getModelKey($viaModel, $linkValues);
				$map[$key2][$key1] = true;
			}
		}

		$buckets = [];
		$linkKeys = array_keys($link);

		if (isset($map)) {
			foreach ($models as $i => $model) {
				$key = $this->getModelKey($model, $linkKeys);
				if (isset($map[$key])) {
					foreach (array_keys($map[$key]) as $key2) {
						if ($this->indexBy !== null) {
							$buckets[$key2][$i] = $model;
						} else {
							$buckets[$key2][] = $model;
						}
					}
				}
			}
		} else {
			foreach ($models as $i => $model) {
				$key = $this->getModelKey($model, $linkKeys);
				if ($this->indexBy !== null) {
					$buckets[$key][$i] = $model;
				} else {
					$buckets[$key][] = $model;
				}
			}
		}

		if (!$this->multiple) {
			foreach ($buckets as $i => $bucket) {
				$buckets[$i] = reset($bucket);
			}
		}
		return $buckets;
	}

	/**
	 * @param array $models
	 */
	private function filterByModels($models)
	{
		$attributes = array_keys($this->link);
		$values = [];
		if (count($attributes) === 1) {
			// single key
			$attribute = reset($this->link);
			foreach ($models as $model) {
				if (($value = $model[$attribute]) !== null) {
					$values[] = $value;
				}
			}
		} else {
			// composite keys
			foreach ($models as $model) {
				$v = [];
				foreach ($this->link as $attribute => $link) {
					$v[$attribute] = $model[$link];
				}
				$values[] = $v;
			}
		}
		$this->andWhere(['in', $attributes, array_unique($values, SORT_REGULAR)]);
	}

	/**
	 * @param ActiveRecord|array $model
	 * @param array $attributes
	 * @return string
	 */
	private function getModelKey($model, $attributes)
	{
		if (count($attributes) > 1) {
			$key = [];
			foreach ($attributes as $attribute) {
				$key[] = $model[$attribute];
			}
			return serialize($key);
		} else {
			$attribute = reset($attributes);
			$key = $model[$attribute];
			return is_scalar($key) ? $key : serialize($key);
		}
	}

	/**
	 * @param array $primaryModels either array of AR instances or arrays
	 * @return array
	 */
	private function findPivotRows($primaryModels)
	{
		if (empty($primaryModels)) {
			return [];
		}
		$this->filterByModels($primaryModels);
		/** @var ActiveRecord $primaryModel */
		$primaryModel = reset($primaryModels);
		if (!$primaryModel instanceof ActiveRecordInterface) {
			// when primaryModels are array of arrays (asArray case)
			$primaryModel = new $this->modelClass;
		}
		return $this->asArray()->all($primaryModel->getDb());
	}
}
