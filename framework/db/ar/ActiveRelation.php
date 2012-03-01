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

use yii\db\dao\BaseQuery;

/**
 * ActiveRelation represents the specification of a relation declared in [[ActiveRecord::relations()]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveRelation extends BaseQuery
{
	/**
	 * @var string the name of this relation
	 */
	public $name;
	/**
	 * @var string the name of the model class that this relation represents
	 */
	public $modelClass;
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
	 * @var string the table alias used for the corresponding table during query
	 */
	public $tableAlias;
	/**
	 * @var string the name of the column that the result should be indexed by.
	 * This is only useful when [[hasMany]] is true.
	 */
	public $indexBy;
	/**
	 * @var string the ON clause of the join query
	 */
	public $on;
	/**
	 * @var string
	 */
	public $via;
	/**
	 * @var array the relations that should be queried together (eager loading)
	 */
	public $with;
	/**
	 * @var array the relations that should be used as filters for this query
	 */
	public $filters;
	/**
	 * @var array the scopes that should be applied during query
	 */
	public $scopes;
}
