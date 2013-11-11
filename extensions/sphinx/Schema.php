<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

/**
 * Class Schema
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Schema extends \yii\db\mysql\Schema
{
	/**
	 * Creates a query builder for the database.
	 * This method may be overridden by child classes to create a DBMS-specific query builder.
	 * @return QueryBuilder query builder instance
	 */
	public function createQueryBuilder()
	{
		return new QueryBuilder($this->db);
	}
}