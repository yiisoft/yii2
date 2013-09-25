<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\redis;

use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\db\TableSchema;
use yii\helpers\StringHelper;

/**
 * ActiveRecord is the base class for classes representing relational data in terms of objects.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ActiveRecord extends \yii\db\ActiveRecord
{
	/**
	 * @var array cache for TableSchema instances
	 */
	private static $_tables = array();

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
	 * @inheritdoc
	 */
	public static function findBySql($sql, $params = array())
	{
		throw new NotSupportedException('findBySql() is not supported by redis ActiveRecord');
	}

	/**
	 * Updates the whole table using the provided attribute values and conditions.
	 * For example, to change the status to be 1 for all customers whose status is 2:
	 *
	 * ~~~
	 * Customer::updateAll(array('status' => 1), array('id' => 2));
	 * ~~~
	 *
	 * @param array $attributes attribute values (name-value pairs) to be saved into the table
	 * @param array $condition the conditions that will be put in the WHERE part of the UPDATE SQL.
	 * Please refer to [[ActiveQuery::where()]] on how to specify this parameter.
	 * @param array $params this parameter is ignored in redis implementation.
	 * @return integer the number of rows updated
	 */
	public static function updateAll($attributes, $condition = null, $params = array())
	{
		if (empty($attributes)) {
			return 0;
		}
		$db = static::getDb();
		$n=0;
		foreach(static::fetchPks($condition) as $pk) {
			$newPk = $pk;
			$pk = static::buildKey($pk);
			$key = static::tableName() . ':a:' . $pk;
			// save attributes
			$args = array($key);
			foreach($attributes as $attribute => $value) {
				if (isset($newPk[$attribute])) {
					$newPk[$attribute] = $value;
				}
				$args[] = $attribute;
				$args[] = $value;
			}
			$newPk = static::buildKey($newPk);
			$newKey = static::tableName() . ':a:' . $newPk;
			// rename index if pk changed
			if ($newPk != $pk) {
				$db->executeCommand('MULTI');
				$db->executeCommand('HMSET', $args);
				$db->executeCommand('LINSERT', array(static::tableName(), 'AFTER', $pk, $newPk));
				$db->executeCommand('LREM', array(static::tableName(), 0, $pk));
				$db->executeCommand('RENAME', array($key, $newKey));
				$db->executeCommand('EXEC');
			} else {
				$db->executeCommand('HMSET', $args);
			}
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
	 * @param array $condition the conditions that will be put in the WHERE part of the UPDATE SQL.
	 * Please refer to [[ActiveQuery::where()]] on how to specify this parameter.
	 * @param array $params this parameter is ignored in redis implementation.
	 * @return integer the number of rows updated
	 */
	public static function updateAllCounters($counters, $condition = null, $params = array())
	{
		if (empty($counters)) {
			return 0;
		}
		$db = static::getDb();
		$n=0;
		foreach(static::fetchPks($condition) as $pk) {
			$key = static::tableName() . ':a:' . static::buildKey($pk);
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
	 * @param array $condition the conditions that will be put in the WHERE part of the DELETE SQL.
	 * Please refer to [[ActiveQuery::where()]] on how to specify this parameter.
	 * @param array $params this parameter is ignored in redis implementation.
	 * @return integer the number of rows deleted
	 */
	public static function deleteAll($condition = null, $params = array())
	{
		$db = static::getDb();
		$attributeKeys = array();
		$pks = static::fetchPks($condition);
		$db->executeCommand('MULTI');
		foreach($pks as $pk) {
			$pk = static::buildKey($pk);
			$db->executeCommand('LREM', array(static::tableName(), 0, $pk));
			$attributeKeys[] = static::tableName() . ':a:' . $pk;
		}
		if (empty($attributeKeys)) {
			$db->executeCommand('EXEC');
			return 0;
		}
		$db->executeCommand('DEL', $attributeKeys);
		$result = $db->executeCommand('EXEC');
		return end($result);
	}

	private static function fetchPks($condition)
	{
		$query = static::createQuery();
		$query->where($condition);
		$records = $query->asArray()->all(); // TODO limit fetched columns to pk
		$primaryKey = static::primaryKey();

		$pks = array();
		foreach($records as $record) {
			$pk = array();
			foreach($primaryKey as $key) {
				$pk[$key] = $record[$key];
			}
			$pks[] = $pk;
		}
		return $pks;
	}

	/**
	 * Builds a normalized key from a given primary key value.
	 *
	 * @param mixed $key the key to be normalized
	 * @return string the generated key
	 */
	public static function buildKey($key)
	{
		if (is_numeric($key)) {
			return $key;
		} elseif (is_string($key)) {
			return ctype_alnum($key) && StringHelper::strlen($key) <= 32 ? $key : md5($key);
		} elseif (is_array($key)) {
			if (count($key) == 1) {
				return self::buildKey(reset($key));
			}
			$isNumeric = true;
			foreach($key as $value) {
				if (!is_numeric($value)) {
					$isNumeric = false;
				}
			}
			if ($isNumeric) {
				return implode('-', $key);
			}
		}
		return md5(json_encode($key));
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
	 * Declares the name of the database table associated with this AR class.
	 * @return string the table name
	 */
	public static function tableName()
	{
		return static::getTableSchema()->name;
	}

	/**
	 * This method is ment to be overridden in redis ActiveRecord subclasses to return a [[RecordSchema]] instance.
	 * @return RecordSchema
	 * @throws \yii\base\InvalidConfigException
	 */
	public static function getRecordSchema()
	{
		throw new InvalidConfigException(__CLASS__.'::getRecordSchema() needs to be overridden in subclasses and return a RecordSchema.');
	}

	/**
	 * Returns the schema information of the DB table associated with this AR class.
	 * @return TableSchema the schema information of the DB table associated with this AR class.
	 */
	public static function getTableSchema()
	{
		$class = get_called_class();
		if (isset(self::$_tables[$class])) {
			return self::$_tables[$class];
		}
		return self::$_tables[$class] = static::getRecordSchema();
	}


	/**
	 * Declares a `has-one` relation.
	 * The declaration is returned in terms of an [[ActiveRelation]] instance
	 * through which the related record can be queried and retrieved back.
	 *
	 * A `has-one` relation means that there is at most one related record matching
	 * the criteria set by this relation, e.g., a customer has one country.
	 *
	 * For example, to declare the `country` relation for `Customer` class, we can write
	 * the following code in the `Customer` class:
	 *
	 * ~~~
	 * public function getCountry()
	 * {
	 *     return $this->hasOne('Country', array('id' => 'country_id'));
	 * }
	 * ~~~
	 *
	 * Note that in the above, the 'id' key in the `$link` parameter refers to an attribute name
	 * in the related class `Country`, while the 'country_id' value refers to an attribute name
	 * in the current AR class.
	 *
	 * Call methods declared in [[ActiveRelation]] to further customize the relation.
	 *
	 * @param string $class the class name of the related record
	 * @param array $link the primary-foreign key constraint. The keys of the array refer to
	 * the columns in the table associated with the `$class` model, while the values of the
	 * array refer to the corresponding columns in the table associated with this AR class.
	 * @return ActiveRelation the relation object.
	 */
	public function hasOne($class, $link)
	{
		return new ActiveRelation(array(
			'modelClass' => $this->getNamespacedClass($class),
			'primaryModel' => $this,
			'link' => $link,
			'multiple' => false,
		));
	}

	/**
	 * Declares a `has-many` relation.
	 * The declaration is returned in terms of an [[ActiveRelation]] instance
	 * through which the related record can be queried and retrieved back.
	 *
	 * A `has-many` relation means that there are multiple related records matching
	 * the criteria set by this relation, e.g., a customer has many orders.
	 *
	 * For example, to declare the `orders` relation for `Customer` class, we can write
	 * the following code in the `Customer` class:
	 *
	 * ~~~
	 * public function getOrders()
	 * {
	 *     return $this->hasMany('Order', array('customer_id' => 'id'));
	 * }
	 * ~~~
	 *
	 * Note that in the above, the 'customer_id' key in the `$link` parameter refers to
	 * an attribute name in the related class `Order`, while the 'id' value refers to
	 * an attribute name in the current AR class.
	 *
	 * @param string $class the class name of the related record
	 * @param array $link the primary-foreign key constraint. The keys of the array refer to
	 * the columns in the table associated with the `$class` model, while the values of the
	 * array refer to the corresponding columns in the table associated with this AR class.
	 * @return ActiveRelation the relation object.
	 */
	public function hasMany($class, $link)
	{
		return new ActiveRelation(array(
			'modelClass' => $this->getNamespacedClass($class),
			'primaryModel' => $this,
			'link' => $link,
			'multiple' => true,
		));
	}

	/**
	 * @inheritDocs
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
//			if ($values === array()) {
			foreach ($this->primaryKey() as $key) {
				$pk[$key] = $values[$key] = $this->getAttribute($key);
				if ($pk[$key] === null) {
					$pk[$key] = $values[$key] = $db->executeCommand('INCR', array(static::tableName() . ':s:' . $key));
					$this->setAttribute($key, $values[$key]);
				}
			}
//			}
			// save pk in a findall pool
			$db->executeCommand('RPUSH', array(static::tableName(), static::buildKey($pk)));

			$key = static::tableName() . ':a:' . static::buildKey($pk);
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
	 * Returns a value indicating whether the specified operation is transactional in the current [[scenario]].
	 * This method will always return false as transactional operations are not supported by redis.
	 * @param integer $operation the operation to check. Possible values are [[OP_INSERT]], [[OP_UPDATE]] and [[OP_DELETE]].
	 * @return boolean whether the specified operation is transactional in the current [[scenario]].
	 */
	public function isTransactional($operation)
	{
		return false;
	}
}
