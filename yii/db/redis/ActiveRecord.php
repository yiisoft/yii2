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

use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;
use yii\base\UnknownMethodException;
use yii\db\TableSchema;

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
		throw new InvalidConfigException(__CLASS__.'::getTableSchema() needs to be overridden in subclasses and return a TableSchema.');
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
			$db->executeCommand('RPUSH', array(static::tableName(), implode('-', $pk))); // TODO escape PK glue

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
		if (is_array($condition) && !isset($condition[0])) { // TODO do this in all *All methods
			$condition = array($condition);
		}
		$db = static::getDb();
		if ($condition==='') {
			$condition = $db->executeCommand('LRANGE', array(static::tableName(), 0, -1));
		}
		$n=0;
		foreach($condition as $pk) { // TODO allow multiple pks as condition
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
			$attributeKeys[] = static::tableName() . ':a:' . $pk; // TODO escape PK glue
		}
		return $db->executeCommand('DEL', $attributeKeys);
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
	 * Returns the relation object with the specified name.
	 * A relation is defined by a getter method which returns an [[ActiveRelation]] object.
	 * It can be declared in either the Active Record class itself or one of its behaviors.
	 * @param string $name the relation name
	 * @return ActiveRelation the relation object
	 * @throws InvalidParamException if the named relation does not exist.
	 */
	public function getRelation($name)
	{
		$getter = 'get' . $name;
		try {
			$relation = $this->$getter();
			if ($relation instanceof ActiveRelation) {
				return $relation;
			}
		} catch (UnknownMethodException $e) {
		}
		throw new InvalidParamException(get_class($this) . ' has no relation named "' . $name . '".');
	}

	/**
	 * Establishes the relationship between two models.
	 *
	 * The relationship is established by setting the foreign key value(s) in one model
	 * to be the corresponding primary key value(s) in the other model.
	 * The model with the foreign key will be saved into database without performing validation.
	 *
	 * If the relationship involves a pivot table, a new row will be inserted into the
	 * pivot table which contains the primary key values from both models.
	 *
	 * Note that this method requires that the primary key value is not null.
	 *
	 * @param string $name the name of the relationship
	 * @param ActiveRecord $model the model to be linked with the current one.
	 * @param array $extraColumns additional column values to be saved into the pivot table.
	 * This parameter is only meaningful for a relationship involving a pivot table
	 * (i.e., a relation set with `[[ActiveRelation::via()]]` or `[[ActiveRelation::viaTable()]]`.)
	 * @throws InvalidCallException if the method is unable to link two models.
	 */
	public function link($name, $model, $extraColumns = array())
	{
		// TODO
		$relation = $this->getRelation($name);

		if ($relation->via !== null) {
			if (is_array($relation->via)) {
				/** @var $viaRelation ActiveRelation */
				list($viaName, $viaRelation) = $relation->via;
				/** @var $viaClass ActiveRecord */
				$viaClass = $viaRelation->modelClass;
				$viaTable = $viaClass::tableName();
				// unset $viaName so that it can be reloaded to reflect the change
				unset($this->_related[strtolower($viaName)]);
			} else {
				$viaRelation = $relation->via;
				$viaTable = reset($relation->via->from);
			}
			$columns = array();
			foreach ($viaRelation->link as $a => $b) {
				$columns[$a] = $this->$b;
			}
			foreach ($relation->link as $a => $b) {
				$columns[$b] = $model->$a;
			}
			foreach ($extraColumns as $k => $v) {
				$columns[$k] = $v;
			}
			static::getDb()->createCommand()
				->insert($viaTable, $columns)->execute();
		} else {
			$p1 = $model->isPrimaryKey(array_keys($relation->link));
			$p2 = $this->isPrimaryKey(array_values($relation->link));
			if ($p1 && $p2) {
				if ($this->getIsNewRecord() && $model->getIsNewRecord()) {
					throw new InvalidCallException('Unable to link models: both models are newly created.');
				} elseif ($this->getIsNewRecord()) {
					$this->bindModels(array_flip($relation->link), $this, $model);
				} else {
					$this->bindModels($relation->link, $model, $this);
				}
			} elseif ($p1) {
				$this->bindModels(array_flip($relation->link), $this, $model);
			} elseif ($p2) {
				$this->bindModels($relation->link, $model, $this);
			} else {
				throw new InvalidCallException('Unable to link models: the link does not involve any primary key.');
			}
		}

		// update lazily loaded related objects
		if (!$relation->multiple) {
			$this->_related[$name] = $model;
		} elseif (isset($this->_related[$name])) {
			if ($relation->indexBy !== null) {
				$indexBy = $relation->indexBy;
				$this->_related[$name][$model->$indexBy] = $model;
			} else {
				$this->_related[$name][] = $model;
			}
		}
	}

	/**
	 * Destroys the relationship between two models.
	 *
	 * The model with the foreign key of the relationship will be deleted if `$delete` is true.
	 * Otherwise, the foreign key will be set null and the model will be saved without validation.
	 *
	 * @param string $name the name of the relationship.
	 * @param ActiveRecord $model the model to be unlinked from the current one.
	 * @param boolean $delete whether to delete the model that contains the foreign key.
	 * If false, the model's foreign key will be set null and saved.
	 * If true, the model containing the foreign key will be deleted.
	 * @throws InvalidCallException if the models cannot be unlinked
	 */
	public function unlink($name, $model, $delete = false)
	{
		// TODO
		$relation = $this->getRelation($name);

		if ($relation->via !== null) {
			if (is_array($relation->via)) {
				/** @var $viaRelation ActiveRelation */
				list($viaName, $viaRelation) = $relation->via;
				/** @var $viaClass ActiveRecord */
				$viaClass = $viaRelation->modelClass;
				$viaTable = $viaClass::tableName();
				unset($this->_related[strtolower($viaName)]);
			} else {
				$viaRelation = $relation->via;
				$viaTable = reset($relation->via->from);
			}
			$columns = array();
			foreach ($viaRelation->link as $a => $b) {
				$columns[$a] = $this->$b;
			}
			foreach ($relation->link as $a => $b) {
				$columns[$b] = $model->$a;
			}
			$command = static::getDb()->createCommand();
			if ($delete) {
				$command->delete($viaTable, $columns)->execute();
			} else {
				$nulls = array();
				foreach (array_keys($columns) as $a) {
					$nulls[$a] = null;
				}
				$command->update($viaTable, $nulls, $columns)->execute();
			}
		} else {
			$p1 = $model->isPrimaryKey(array_keys($relation->link));
			$p2 = $this->isPrimaryKey(array_values($relation->link));
			if ($p1 && $p2 || $p2) {
				foreach ($relation->link as $a => $b) {
					$model->$a = null;
				}
				$delete ? $model->delete() : $model->save(false);
			} elseif ($p1) {
				foreach ($relation->link as $b) {
					$this->$b = null;
				}
				$delete ? $this->delete() : $this->save(false);
			} else {
				throw new InvalidCallException('Unable to unlink models: the link does not involve any primary key.');
			}
		}

		if (!$relation->multiple) {
			unset($this->_related[$name]);
		} elseif (isset($this->_related[$name])) {
			/** @var $b ActiveRecord */
			foreach ($this->_related[$name] as $a => $b) {
				if ($model->getPrimaryKey() == $b->getPrimaryKey()) {
					unset($this->_related[$name][$a]);
				}
			}
		}
	}


	// TODO implement link and unlink
}
