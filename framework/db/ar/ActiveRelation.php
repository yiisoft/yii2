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
	 * @var string
	 */
	public $via;
}
