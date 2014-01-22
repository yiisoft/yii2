<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

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
 * If a relation involves a pivot table, it may be specified by [[via()]] method.
 *
 * NOTE: elasticsearch limits the number of records returned by any query to 10 records by default.
 * If you expect to get more records you should specify limit explicitly in relation definition.
 * This is also important for relations that use [[via()]] so that if via records are limited to 10
 * the relations records can also not be more than 10.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ActiveRelation extends ActiveQuery implements ActiveRelationInterface
{
	use ActiveRelationTrait;

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
			if (is_array($this->via)) {
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
