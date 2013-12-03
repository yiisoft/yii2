<?php
/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * ActiveRelation represents a relation between two Active Record classes.
 *
 * ActiveRelation instances are usually created by calling [[ActiveRecord::hasOne()]] and
 * [[ActiveRecord::hasMany()]]. An Active Record class declares a relation by defining
 * a getter method which calls one of the above methods and returns the created ActiveRelation object.
 *
 * A relation is specified by [[link]] which represents the association between columns
 * of different tables; and the multiplicity of the relation is indicated by [[multiple]].
 *
 * If a relation involves a pivot table, it may be specified by [[via()]] or [[viaTable()]] method.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ActiveRelation extends ActiveQuery implements ActiveRelationInterface
{
	use ActiveRelationTrait;

	/**
	 * Specifies the pivot table.
	 * @param string $tableName the name of the pivot table.
	 * @param array $link the link between the pivot table and the table associated with [[primaryModel]].
	 * The keys of the array represent the columns in the pivot table, and the values represent the columns
	 * in the [[primaryModel]] table.
	 * @param callable $callable a PHP callback for customizing the relation associated with the pivot table.
	 * Its signature should be `function($query)`, where `$query` is the query to be customized.
	 * @return static
	 */
	public function viaTable($tableName, $link, $callable = null)
	{
		$relation = new ActiveRelation([
			'modelClass' => get_class($this->primaryModel),
			'from' => [$tableName],
			'link' => $link,
			'multiple' => true,
			'asArray' => true,
		]);
		$this->via = $relation;
		if ($callable !== null) {
			call_user_func($callable, $relation);
		}
		return $this;
	}

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
			if ($this->via instanceof self) {
				// via pivot table
				$viaModels = $this->via->findPivotRows([$this->primaryModel]);
				$this->filterByModels($viaModels);
			} elseif (is_array($this->via)) {
				// via relation
				/** @var ActiveRelation $viaQuery */
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
		return parent::createCommand($db);
	}
}
