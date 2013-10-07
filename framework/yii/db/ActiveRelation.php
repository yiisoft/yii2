<?php
/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\InvalidConfigException;

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
 * @since 2.0
 */
class ActiveRelation extends ActiveQuery
{
	use \yii\ar\BaseActiveRelation;

	/**
	 * @var array|ActiveRelation the query associated with the pivot table. Please call [[via()]]
	 * or [[viaTable()]] to set this property instead of directly setting it.
	 */
	public $via;

	/**
	 * Specifies the pivot table.
	 * @param string $tableName the name of the pivot table.
	 * @param array $link the link between the pivot table and the table associated with [[primaryModel]].
	 * The keys of the array represent the columns in the pivot table, and the values represent the columns
	 * in the [[primaryModel]] table.
	 * @param callable $callable a PHP callback for customizing the relation associated with the pivot table.
	 * Its signature should be `function($query)`, where `$query` is the query to be customized.
	 * @return ActiveRelation
	 */
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
	}

	/**
	 * Creates a DB command that can be used to execute this query.
	 * @param Connection $db the DB connection used to create the DB command.
	 * If null, the DB connection returned by [[modelClass]] will be used.
	 * @return Command the created DB command instance.
	 */
	public function createCommand($db = null)
	{
		$this->prepareCommand();
		return parent::createCommand($db);
	}
}
