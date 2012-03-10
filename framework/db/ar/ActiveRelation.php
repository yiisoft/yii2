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
 * ActiveRelation represents the specification of a relation declared in [[ActiveRecord::relations()]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveRelation extends BaseActiveQuery
{
	/**
	 * @var string the name of this relation
	 */
	public $name;
	/**
	 * @var array the columns of the primary and foreign tables that establish the relation.
	 * The array keys must be columns of the table for this relation, and the array values
	 * must be the corresponding columns from the primary table. Do not prefix or quote the column names.
	 * They will be done automatically by Yii.
	 */
	public $link;
	/**
	 * @var boolean whether this relation is a one-many relation
	 */
	public $hasMany;
	/**
	 * @var string the join type (e.g. INNER JOIN, LEFT JOIN). Defaults to 'LEFT JOIN' when
	 * this relation is used to load related records, and 'INNER JOIN' when this relation is used as a filter.
	 */
	public $joinType;
	/**
	 * @var string the ON clause of the join query
	 */
	public $on;
	/**
	 * @var string|array
	 */
	public $via;
}
