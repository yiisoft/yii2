<?php
/**
 * ActiveRecord class file.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\redis;

/**
 * ActiveRecord is the base class for classes representing relational data in terms of objects.
 *
 * @include @yii/db/ActiveRecord.md
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
abstract class ActiveRecord extends \yii\db\ActiveRecord
{
	/**
	 * Returns the list of all attribute names of the model.
	 * The default implementation will return all column names of the table associated with this AR class.
	 * @return array list of attribute names.
	 */
	public function attributes()
	{
		return array();
	}

	/**
	 * Returns the database connection used by this AR class.
	 * By default, the "db" application component is used as the database connection.
	 * You may override this method if you want to use a different database connection.
	 * @return Connection the database connection used by this AR class.
	 */
	public static function getDb()
	{
		// TODO
		return \Yii::$application->getDb();
	}

	/**
	 * Creates an [[ActiveQuery]] instance for query purpose.
	 *
	 * @include @yii/db/ActiveRecord-find.md
	 *
	 * @param mixed $q the query parameter. This can be one of the followings:
	 *
	 *  - a scalar value (integer or string): query by a single primary key value and return the
	 *    corresponding record.
	 *  - an array of name-value pairs: query by a set of column values and return a single record matching all of them.
	 *  - null: return a new [[ActiveQuery]] object for further query purpose.
	 *
	 * @return ActiveQuery|ActiveRecord|null When `$q` is null, a new [[ActiveQuery]] instance
	 * is returned; when `$q` is a scalar or an array, an ActiveRecord object matching it will be
	 * returned (null will be returned if there is no matching).
	 * @see createQuery()
	 */
	public static function find($q = null)
	{
		// TODO
		$query = static::createQuery();
		if (is_array($q)) {
			return $query->where($q)->one();
		} elseif ($q !== null) {
			// query by primary key
			$primaryKey = static::primaryKey();
			return $query->where(array($primaryKey[0] => $q))->one();
		}
		return $query;
	}

	/**
	 * Creates an [[ActiveQuery]] instance with a given SQL statement.
	 *
	 * Note that because the SQL statement is already specified, calling additional
	 * query modification methods (such as `where()`, `order()`) on the created [[ActiveQuery]]
	 * instance will have no effect. However, calling `with()`, `asArray()` or `indexBy()` is
	 * still fine.
	 *
	 * Below is an example:
	 *
	 * ~~~
	 * $customers = Customer::findBySql('SELECT * FROM tbl_customer')->all();
	 * ~~~
	 *
	 * @param string $sql the SQL statement to be executed
	 * @param array $params parameters to be bound to the SQL statement during execution.
	 * @return ActiveQuery the newly created [[ActiveQuery]] instance
	 */
	public static function findBySql($sql, $params = array())
	{
		$query = static::createQuery();
		$query->sql = $sql;
		return $query->params($params);
	}



	/**
	 * Creates an [[ActiveQuery]] instance.
	 * This method is called by [[find()]], [[findBySql()]] and [[count()]] to start a SELECT query.
	 * You may override this method to return a customized query (e.g. `CustomerQuery` specified
	 * written for querying `Customer` purpose.)
	 * @return ActiveQuery the newly created [[ActiveQuery]] instance.
	 */
	public static function createQuery()
	{
		return new ActiveQuery(array(
			'modelClass' => get_called_class(),
		));
	}


	/**
	 * Returns the schema information of the DB table associated with this AR class.
	 * @return TableSchema the schema information of the DB table associated with this AR class.
	 */
	public static function getTableSchema()
	{
		return static::getDb()->getTableSchema(static::tableName());
	}

	/**
	 * Returns the primary key name(s) for this AR class.
	 * The default implementation will return the primary key(s) as declared
	 * in the DB table that is associated with this AR class.
	 *
	 * If the DB table does not declare any primary key, you should override
	 * this method to return the attributes that you want to use as primary keys
	 * for this AR class.
	 *
	 * Note that an array should be returned even for a table with single primary key.
	 *
	 * @return string[] the primary keys of the associated database table.
	 */
	public static function primaryKey()
	{
		return static::getTableSchema()->primaryKey;
	}

}
