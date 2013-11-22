<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\redis;

use yii\db\ActiveRelationInterface;
use yii\db\ActiveRelationTrait;

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
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ActiveRelation extends ActiveQuery implements ActiveRelationInterface
{
	use ActiveRelationTrait;

	/**
	 * Executes a script created by [[LuaScriptBuilder]]
	 * @param Connection $db the database connection used to execute the query.
	 * If this parameter is not given, the `db` application component will be used.
	 * @param string $type the type of the script to generate
	 * @param null $column
	 * @return array|bool|null|string
	 */
	protected function executeScript($db, $type, $column=null)
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
		return parent::executeScript($db, $type, $column);
	}
}
