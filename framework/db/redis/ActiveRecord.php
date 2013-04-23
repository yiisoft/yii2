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

use yii\base\NotSupportedException;

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
	public function attributes() // TODO: refactor should be abstract in an ActiveRecord base class
	{
		return array();
	}

	/**
	 * Returns the database connection used by this AR class.
	 * By default, the "redis" application component is used as the database connection.
	 * You may override this method if you want to use a different database connection.
	 * @return Connection the database connection used by this AR class.
	 */
	public static function getDb()
	{
		return \Yii::$app->redis;
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
	public static function find($q = null) // TODO optimize API
	{
		$query = static::createQuery();
		if (is_array($q)) {
			return $query->primaryKeys($q)->one();
		} elseif ($q !== null) {
			// query by primary key
			$primaryKey = static::primaryKey();
			return $query->primaryKeys(array($primaryKey[0] => $q))->one();
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
		throw new NotSupportedException('findBySql() is not supported by redis ActiveRecord');
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
		throw new NotSupportedException('getTableSchema() is not supported by redis ActiveRecord as there is no schema in redis DB. Schema is defined by AR class itself');
	}

	/**
	 * Inserts a row into the associated database table using the attribute values of this record.
	 *
	 * This method performs the following steps in order:
	 *
	 * 1. call [[beforeValidate()]] when `$runValidation` is true. If validation
	 *    fails, it will skip the rest of the steps;
	 * 2. call [[afterValidate()]] when `$runValidation` is true.
	 * 3. call [[beforeSave()]]. If the method returns false, it will skip the
	 *    rest of the steps;
	 * 4. insert the record into database. If this fails, it will skip the rest of the steps;
	 * 5. call [[afterSave()]];
	 *
	 * In the above step 1, 2, 3 and 5, events [[EVENT_BEFORE_VALIDATE]],
	 * [[EVENT_BEFORE_INSERT]], [[EVENT_AFTER_INSERT]] and [[EVENT_AFTER_VALIDATE]]
	 * will be raised by the corresponding methods.
	 *
	 * Only the [[changedAttributes|changed attribute values]] will be inserted into database.
	 *
	 * If the table's primary key is auto-incremental and is null during insertion,
	 * it will be populated with the actual value after insertion.
	 *
	 * For example, to insert a customer record:
	 *
	 * ~~~
	 * $customer = new Customer;
	 * $customer->name = $name;
	 * $customer->email = $email;
	 * $customer->insert();
	 * ~~~
	 *
	 * @param boolean $runValidation whether to perform validation before saving the record.
	 * If the validation fails, the record will not be inserted into the database.
	 * @param array $attributes list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved.
	 * @return boolean whether the attributes are valid and the record is inserted successfully.
	 */
	public function insert($runValidation = true, $attributes = null)
	{
		if ($runValidation && !$this->validate($attributes)) {
			return false;
		}
		if ($this->beforeSave(true)) {
			$db = static::getDb();
			$values = $this->getDirtyAttributes($attributes);
			$pk = array();
			if ($values === array()) {
				foreach ($this->primaryKey() as $key) {
					$pk[$key] = $values[$key] = $this->getAttribute($key);
					if ($pk[$key] === null) {
						$pk[$key] = $db->executeCommand('INCR', array(static::tableName() . ':s:', $key));
					}
				}
			}
			// save pk in a findall pool
			$db->executeCommand('RPUSH', array(static::tableName(), $pk));

			$key = static::tableName() . ':a:' . implode('-', $pk); // TODO escape PK glue
			// save attributes
			$args = array($key);
			foreach($values as $attribute => $value) {
				$args[] = $attribute;
				$args[] = $value;
			}
			$db->executeCommand('HMSET', $args);

			$this->setOldAttributes($values);
			$this->afterSave(true);
			return true;
		}
		return false;
	}

	/**
	 * Updates the whole table using the provided attribute values and conditions.
	 * For example, to change the status to be 1 for all customers whose status is 2:
	 *
	 * ~~~
	 * Customer::updateAll(array('status' => 1), 'status = 2');
	 * ~~~
	 *
	 * @param array $attributes attribute values (name-value pairs) to be saved into the table
	 * @param string|array $condition the conditions that will be put in the WHERE part of the UPDATE SQL.
	 * Please refer to [[Query::where()]] on how to specify this parameter.
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * @return integer the number of rows updated
	 */
	public static function updateAll($attributes, $condition = '', $params = array())
	{
		$db = static::getDb();
		if ($condition==='') {
			$condition = $db->executeCommand('LRANGE', array(static::tableName(), 0, -1));
		}
		if (empty($attributes)) {
			return 0;
		}
		$n=0;
		foreach($condition as $pk) {
			$key = static::tableName() . ':a:' . (is_array($pk) ? implode('-', $pk) : $pk); // TODO escape PK glue
			// save attributes
			$args = array($key);
			foreach($attributes as $attribute => $value) {
				$args[] = $attribute;
				$args[] = $value;
			}
			$db->executeCommand('HMSET', $args);
			$n++;
		}

		return $n;
	}

	/**
	 * Updates the whole table using the provided counter changes and conditions.
	 * For example, to increment all customers' age by 1,
	 *
	 * ~~~
	 * Customer::updateAllCounters(array('age' => 1));
	 * ~~~
	 *
	 * @param array $counters the counters to be updated (attribute name => increment value).
	 * Use negative values if you want to decrement the counters.
	 * @param string|array $condition the conditions that will be put in the WHERE part of the UPDATE SQL.
	 * Please refer to [[Query::where()]] on how to specify this parameter.
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * Do not name the parameters as `:bp0`, `:bp1`, etc., because they are used internally by this method.
	 * @return integer the number of rows updated
	 */
	public static function updateAllCounters($counters, $condition = '', $params = array())
	{
		$db = static::getDb();
		if ($condition==='') {
			$condition = $db->executeCommand('LRANGE', array(static::tableName(), 0, -1));
		}
		$n=0;
		foreach($condition as $pk) {
			$key = static::tableName() . ':a:' . (is_array($pk) ? implode('-', $pk) : $pk); // TODO escape PK glue
			foreach($counters as $attribute => $value) {
				$db->executeCommand('HINCRBY', array($key, $attribute, $value));
			}
			$n++;
		}
		return $n;
	}

	/**
	 * Deletes rows in the table using the provided conditions.
	 * WARNING: If you do not specify any condition, this method will delete ALL rows in the table.
	 *
	 * For example, to delete all customers whose status is 3:
	 *
	 * ~~~
	 * Customer::deleteAll('status = 3');
	 * ~~~
	 *
	 * @param string|array $condition the conditions that will be put in the WHERE part of the DELETE SQL.
	 * Please refer to [[Query::where()]] on how to specify this parameter.
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * @return integer the number of rows deleted
	 */
	public static function deleteAll($condition = '', $params = array())
	{
		$db = static::getDb();
		if ($condition==='') {
			$condition = $db->executeCommand('LRANGE', array(static::tableName(), 0, -1));
		}
		if (empty($condition)) {
			return 0;
		}
		$attributeKeys = array();
		foreach($condition as $pk) {
			if (is_array($pk)) {
				$pk = implode('-', $pk);
			}
			$db->executeCommand('LREM', array(static::tableName(), 0, $pk)); // TODO escape PK glue
			$attributeKeys[] = static::tableName() . ':' . $pk . ':a'; // TODO escape PK glue
		}
		return $db->executeCommand('DEL', $attributeKeys);
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
	public static function primaryKey() // TODO: refactor should be abstract in an ActiveRecord base class
	{
		return array();
	}

	// TODO implement link and unlink
}
