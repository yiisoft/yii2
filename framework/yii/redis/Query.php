<?php
/**
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yii\redis;


use yii\base\Component;
use yii\db\BaseQuery;

abstract class Query extends Component
{
	use BaseQuery;

	/**
	 * Sort ascending
	 * @see orderBy
	 */
	const SORT_ASC = false; // TODO where to put these
	/**
	 * Sort descending
	 * @see orderBy
	 */
	const SORT_DESC = true;

	/**
	 * @var array the query condition.
	 * @see where()
	 */
	public $where;
}