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
		$this->via = array($modelClass, $properties);
		return $this;
	}

	public function viaTable($tableName, $link, $properties = array())
	{
		$this->viaTable = array($tableName, $link, $properties);
		return $this;
	}

	public function createCommand($db = null)
	{
		if ($this->primaryModel !== null) {
			$this->filterByPrimaryModels(array($this->primaryModel));
		}
		return parent::createCommand($db);
	}

	public function findWith($name, &$primaryModels)
	{
		if (!is_array($this->link)) {
			throw new \yii\base\Exception('invalid link');
		}

		if ($this->via !== null) {
		}

		$this->filterByPrimaryModels($primaryModels);

		if (count($primaryModels) === 1 && !$this->multiple) {
			foreach ($primaryModels as $i => $primaryModel) {
				$primaryModels[$i][$name] = $this->one();
			}
		} else {
			$models = $this->all();
			$this->bindModels($name, $primaryModels, $models);
		}
	}

	protected function bindModels($name, &$primaryModels, $models)
	{
		$buckets = array();
		foreach ($models as $i => $model) {
			$key = $this->getModelKey($model, array_keys($this->link));
			if ($this->index !== null) {
				$buckets[$key][$i] = $model;
			} else {
				$buckets[$key][] = $model;
			}
		}
		if (!$this->multiple) {
			foreach ($buckets as $i => $bucket) {
				$buckets[$i] = reset($bucket);
			}
		}
		foreach ($primaryModels as $i => $primaryModel) {
			$key = $this->getModelKey($primaryModel, array_values($this->link));
			if (isset($buckets[$key])) {
				$primaryModels[$i][$name] = $buckets[$key];
			} else {
				$primaryModels[$i][$name] = $this->multiple ? array() : null;
			}
		}
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

	protected function filterByPrimaryModels($primaryModels)
	{
		$attributes = array_keys($this->link);
		$values = array();
		if (isset($links[1])) {
			// composite keys
			foreach ($primaryModels as $model) {
				$v = array();
				foreach ($this->link as $attribute => $link) {
					$v[$attribute] = is_array($model) ? $model[$link] : $model->$link;
				}
				$values[] = $v;
			}
		} else {
			// single key
			$attribute = $this->link[$links[0]];
			foreach ($primaryModels as $model) {
				$values[] = is_array($model) ? $model[$attribute] : $model->$attribute;
			}
		}
		$this->andWhere(array('in', $attributes, $values));
	}

}
