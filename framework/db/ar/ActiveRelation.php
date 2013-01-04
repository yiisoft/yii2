<?php
/**
 * ActiveRelation class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\ar;

/**
 * It is used in three scenarios:
 * - eager loading: User::find()->with('posts')->all();
 * - lazy loading: $user->posts;
 * - lazy loading with query options: $user->posts()->where('status=1')->get();
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveRelation extends ActiveQuery
{
	/**
	 * @var ActiveRecord the primary model that this relation is associated with.
	 * This is used only in lazy loading with dynamic query options.
	 */
	public $primaryModel;
	/**
	 * @var boolean whether this relation should populate all query results into AR instances.
	 * If false, only the first row of the results will be taken.
	 */
	public $multiple;
	/**
	 * @var array the columns of the primary and foreign tables that establish the relation.
	 * The array keys must be columns of the table for this relation, and the array values
	 * must be the corresponding columns from the primary table.
	 * Do not prefix or quote the column names as they will be done automatically by Yii.
	 */
	public $link;
	/**
	 * @var array
	 */
	public $via;
	/**
	 * @var array
	 */
	public $viaTable;

	public function via($modelClass, $properties = array())
	{
		$this->via = $modelClass;
		return $this;
	}

	public function viaTable($tableName, $link, $properties = array())
	{
		$this->viaTable = array($tableName, $link, $properties);
		return $this;
	}

	public function createCommand()
	{
		if ($this->primaryModel !== null) {
			if ($this->via !== null) {
				/** @var $viaQuery ActiveRelation */
				$viaName = $this->via;
				$viaModels = $this->primaryModel->$viaName;
				if ($viaModels === null) {
					$viaModels = array();
				} elseif (!is_array($viaModels)) {
					$viaModels = array($viaModels);
				}
				$this->filterByModels($viaModels);
			} else {
				$this->filterByModels(array($this->primaryModel));
			}
		}
		return parent::createCommand();
	}

	public function findWith($name, &$primaryModels, $viaQuery = null)
	{
		if (!is_array($this->link)) {
			throw new \yii\base\Exception('invalid link');
		}

		if ($viaQuery !== null) {
			$viaModels = $viaQuery->findWith($this->via, $primaryModels);
			$this->filterByModels($viaModels);
		} else {
			$this->filterByModels($primaryModels);
		}

		if (count($primaryModels) === 1 && !$this->multiple) {
			$model = $this->one();
			foreach ($primaryModels as $i => $primaryModel) {
				$primaryModels[$i][$name] = $model;
			}
			return array($model);
		} else {
			$models = $this->all();
			if (isset($viaModels, $viaQuery)) {
				$buckets = $this->buildBuckets($models, $this->link, $viaModels, $viaQuery->link);
			} else {
				$buckets = $this->buildBuckets($models, $this->link);
			}

			foreach ($primaryModels as $i => $primaryModel) {
				if (isset($viaQuery)) {
					$key = $this->getModelKey($primaryModel, array_values($viaQuery->link));
				} else {
					$key = $this->getModelKey($primaryModel, array_values($this->link));
				}
				if (isset($buckets[$key])) {
					$primaryModels[$i][$name] = $buckets[$key];
				} else {
					$primaryModels[$i][$name] = $this->multiple ? array() : null;
				}
			}
			return $models;
		}
	}

	protected function buildBuckets($models, $link, $viaModels = null, $viaLink = null)
	{
		$buckets = array();
		foreach ($models as $i => $model) {
			$key = $this->getModelKey($model, array_keys($link));
			if ($this->index !== null) {
				$buckets[$key][$i] = $model;
			} else {
				$buckets[$key][] = $model;
			}
		}

		if ($viaModels !== null) {
			$viaBuckets = array();
			foreach ($viaModels as $viaModel) {
				$key1 = $this->getModelKey($viaModel, array_keys($viaLink));
				$key2 = $this->getModelKey($viaModel, array_values($link));
				if (isset($buckets[$key2])) {
					foreach ($buckets[$key2] as $i => $bucket) {
						if ($this->index !== null) {
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

	protected function getModelKey($model, $attributes)
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

	protected function filterByModels($models)
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
		$this->andWhere(array('in', $attributes, $values));
	}

}
