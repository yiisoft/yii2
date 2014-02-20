<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;
use yii\base\InvalidParamException;

/**
 * ActiveRelationInterface defines the common interface to be implemented by relational active record query classes.
 *
 * A class implementing this interface should also use [[ActiveRelationTrait]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
interface ActiveRelationInterface extends ActiveQueryInterface
{
	/**
	 * Specifies the relation associated with the pivot table.
	 * @param string $relationName the relation name. This refers to a relation declared in the [[ActiveRelationTrait::primaryModel|primaryModel]] of the relation.
	 * @param callable $callable a PHP callback for customizing the relation associated with the pivot table.
	 * Its signature should be `function($query)`, where `$query` is the query to be customized.
	 * @return static the relation object itself.
	 */
	public function via($relationName, $callable = null);

	/**
	 * Finds the related records for the specified primary record.
	 * This method is invoked when a relation of an ActiveRecord is being accessed in a lazy fashion.
	 * @param string $name the relation name
	 * @param ActiveRecordInterface $model the primary model
	 * @return mixed the related record(s)
	 * @throws InvalidParamException if the relation is invalid
	 */
	public function findFor($name, $model);
}
