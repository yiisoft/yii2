<?php

namespace yii\db\ar;

class ActiveRelation extends \yii\base\Object
{
	public $name;
	public $modelClass;
	public $hasMany;

	public $joinType;
	public $tableAlias;
	public $on;
	public $via;
	public $with;
	public $scopes;

	/**
	 * @var string|array the columns being selected. This refers to the SELECT clause in a SQL
	 * statement. It can be either a string (e.g. `'id, name'`) or an array (e.g. `array('id', 'name')`).
	 * If not set, if means all columns.
	 * @see select()
	 */
	public $select;
	/**
	 * @var string|array query condition. This refers to the WHERE clause in a SQL statement.
	 * For example, `age > 31 AND team = 1`.
	 * @see where()
	 */
	public $where;
	/**
	 * @var integer maximum number of records to be returned. If not set or less than 0, it means no limit.
	 */
	public $limit;
	/**
	 * @var integer zero-based offset from where the records are to be returned. If not set or
	 * less than 0, it means starting from the beginning.
	 */
	public $offset;
	/**
	 * @var string|array how to sort the query results. This refers to the ORDER BY clause in a SQL statement.
	 * It can be either a string (e.g. `'id ASC, name DESC'`) or an array (e.g. `array('id ASC', 'name DESC')`).
	 */
	public $orderBy;
	/**
	 * @var string|array how to group the query results. This refers to the GROUP BY clause in a SQL statement.
	 * It can be either a string (e.g. `'company, department'`) or an array (e.g. `array('company', 'department')`).
	 */
	public $groupBy;
	/**
	 * @var string|array how to join with other tables. This refers to the JOIN clause in a SQL statement.
	 * It can either a string (e.g. `'LEFT JOIN tbl_user ON tbl_user.id=author_id'`) or an array (e.g.
	 * `array('LEFT JOIN tbl_user ON tbl_user.id=author_id', 'LEFT JOIN tbl_team ON tbl_team.id=team_id')`).
	 * @see join()
	 */
	public $join;
	/**
	 * @var string|array the condition to be applied in the GROUP BY clause.
	 * It can be either a string or an array. Please refer to [[where()]] on how to specify the condition.
	 */
	public $having;
	/**
	 * @var array list of query parameter values indexed by parameter placeholders.
	 * For example, `array(':name'=>'Dan', ':age'=>31)`.
	 */
	public $params;
}
