<?php

namespace yii\db\ar;

class ActiveRelation extends \yii\db\dao\BaseQuery
{
	public $name;
	public $modelClass;
	public $hasMany;

	public $joinType;
	public $alias;
	public $on;
	public $via;
	public $index;
	public $with;
	public $scopes;

	public function mergeWith($relation)
	{
		parent::mergeWith($relation);
		if ($relation->joinType !== null) {
			$this->joinType = $relation->joinType;
		}
		if ($relation->alias !== null) {
			$this->alias = $relation->alias;
		}
		if ($relation->on !== null) {
			if (!empty($this->on)) {
				$this->on = "({$this->on}) AND ({$relation->on})";
			} else {
				$this->on = $relation->on;
			}
		}
		if ($relation->via !== null) {
			$this->via = $relation->via;
		}
		if ($relation->index !== null) {
			$this->index = $relation->index;
		}
		// todo: with, scopes
	}
}
