<?php
/**
 * ActiveRecord class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\Model;
use yii\base\ModelEvent;
use yii\base\BadMethodException;
use yii\base\BadParamException;
use yii\db\Exception;
use yii\db\Connection;
use yii\db\TableSchema;
use yii\db\Expression;
use yii\util\StringHelper;

/**
 * ActiveRecord is the base class for classes representing relational data.
 *
 * @include @yii/db/ActiveRecord.md
 *
 * @property array $attributes attribute values indexed by attribute names
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class ActiveRecord extends Model
{
	/**
	 * @var array attribute values indexed by attribute names
	 */
	private $_attributes = array();
	/**
	 * @var array old attribute values indexed by attribute names.
	 */
	private $_oldAttributes;
	/**
	 * @var array related models indexed by the relation names
	 */
	private $_related;


	/**
	 * Returns the database connection used by this AR class.
	 * By default, the "db" application component is used as the database connection.
	 * You may override this method if you want to use a different database connection.
	 * @return Connection the database connection used by this AR class.
	 */
	public static function getDbConnection()
	{
		return \Yii::$application->getDb();
	}

	/**
	 * Creates an [[ActiveQuery]] instance for query purpose.
	 *
	 * Because [[ActiveQuery]] implements a set of query building methods,
	 * additional query conditions can be specified by calling the methods of [[ActiveQuery]].
	 *
	 * Below are some examples:
	 *
	 * ~~~
	 * // find all customers
	 * $customers = Customer::find()->all();
	 * // find all active customers and order them by their age:
	 * $customers = Customer::find()
	 *     ->where(array('status' => 1))
	 *     ->orderBy('age')
	 *     ->all();
	 * // find a single customer whose primary key value is 10
	 * $customer = Customer::find(10);
	 * // the above is equivalent to:
	 * $customer = Customer::find()->where(array('id' => 10))->one();
	 * // find a single customer whose age is 30 and whose status is 1
	 * $customer = Customer::find(array('age' => 30, 'status' => 1));
	 * // the above is equivalent to:
	 * $customer = Customer::find()->where(array('age' => 30, 'status' => 1))->one();
	 * ~~~
	 *
	 * @param mixed $q the query parameter. This can be one of the followings:
	 *
	 *  - a scalar value (integer or string): query by a single primary key value and return the
	 *    corresponding record.
	 *  - an array of name-value pairs: query by a set of column values and return a single record matching them.
	 *  - null: return a new [[ActiveQuery]] object for further query purpose.
	 *
	 * @return ActiveQuery|ActiveRecord|null When `$q` is null, a new [[ActiveQuery]] instance
	 * is returned; when `$q` is a scalar or an array, an ActiveRecord object matching it will be
	 * returned, or null will be returned if no match is found.
	 */
	public static function find($q = null)
	{
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
	 * Creates an [[ActiveQuery]] instance and queries by a given SQL statement.
	 * Note that because the SQL statement is already specified, calling further
	 * query methods (such as `where()`, `order()`) on [[ActiveQuery]] will have no effect.
	 * Methods such as `with()`, `asArray()` can still be called though.
	 * @param string $sql the SQL statement to be executed
	 * @param array $params parameters to be bound to the SQL statement during execution.
	 * @return ActiveQuery the [[ActiveQuery]] instance
	 */
	public static function findBySql($sql, $params = array())
	{
		$query = static::createQuery();
		$query->sql = $sql;
		return $query->params($params);
	}

	/**
	 * Performs a COUNT query for this AR class.
	 *
	 * Below are some usage examples:
	 *
	 * ~~~
	 * // count the total number of customers
	 * echo Customer::count()->value();
	 * // count the number of active customers:
	 * echo Customer::count()
	 *     ->where(array('status' => 1))
	 *     ->value();
	 * // customize the count expression
	 * echo Customer::count('COUNT(DISTINCT age)')->value();
	 * ~~~
	 *
	 * @param string $q the count expression. If null, it means `COUNT(*)`.
	 *
	 * @return ActiveQuery the [[ActiveQuery]] instance
	 */
	public static function count($q = null)
	{
		$query = static::createQuery();
		if ($q !== null) {
			$query->select = array($q);
		} elseif ($query->select === null) {
			$query->select = array('COUNT(*)');
		}
		return $query;
	}

	/**
	 * Updates the whole table using the provided attribute values and conditions.
	 * @param array $attributes attribute values to be saved into the table
	 * @param string|array $condition the conditions that will be put in the WHERE part.
	 * Please refer to [[Query::where()]] on how to specify this parameter.
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * @return integer the number of rows updated
	 */
	public static function updateAll($attributes, $condition = '', $params = array())
	{
		$command = static::getDbConnection()->createCommand();
		$command->update(static::tableName(), $attributes, $condition, $params);
		return $command->execute();
	}

	/**
	 * Updates the whole table using the provided counter values and conditions.
	 * @param array $counters the counters to be updated (attribute name => increment value).
	 * @param string|array $condition the conditions that will be put in the WHERE part.
	 * Please refer to [[Query::where()]] on how to specify this parameter.
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * @return integer the number of rows updated
	 */
	public static function updateAllCounters($counters, $condition = '', $params = array())
	{
		$db = static::getDbConnection();
		$n = 0;
		foreach ($counters as $name => $value) {
			$quotedName = $db->quoteColumnName($name);
			$counters[$name] = new Expression("$quotedName+:cv{$n}");
			$params[":cv{$n}"] = $value;
			$n++;
		}
		$command = $db->createCommand();
		$command->update(static::tableName(), $counters, $condition, $params);
		return $command->execute();
	}

	/**
	 * Deletes rows in the table using the provided conditions.
	 * @param string|array $condition the conditions that will be put in the WHERE part.
	 * Please refer to [[Query::where()]] on how to specify this parameter.
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * @return integer the number of rows updated
	 */
	public static function deleteAll($condition = '', $params = array())
	{
		$command = static::getDbConnection()->createCommand();
		$command->delete(static::tableName(), $condition, $params);
		return $command->execute();
	}

	/**
	 * Creates a [[ActiveQuery]] instance.
	 * This method is called by [[find()]] and [[findBySql()]] to start a SELECT query.
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
	 * By default this method returns the class name as the table name by calling [[StringHelper::camel2id()]]
	 * with prefix 'tbl_'. For example, 'Customer' becomes 'tbl_customer', and 'OrderDetail' becomes
	 * 'tbl_order_detail'. You may override this method if the table is not named after this convention.
	 * @return string the table name
	 */
	public static function tableName()
	{
		return 'tbl_' . StringHelper::camel2id(basename(get_called_class()), '_');
	}

	/**
	 * Returns the schema information of the DB table associated with this AR class.
	 * @return TableSchema the schema information of the DB table associated with this AR class.
	 */
	public static function getTableSchema()
	{
		return static::getDbConnection()->getTableSchema(static::tableName());
	}

	/**
	 * Returns the primary keys for this AR class.
	 * The default implementation will return the primary keys as declared
	 * in the DB table that is associated with this AR class.
	 * If the DB table does not declare any primary key, you should override
	 * this method to return the attributes that you want to use as primary keys
	 * for this AR class.
	 * @return string[] the primary keys of the associated database table.
	 */
	public static function primaryKey()
	{
		return static::getTableSchema()->primaryKey;
	}

	/**
	 * PHP getter magic method.
	 * This method is overridden so that attributes and related objects can be accessed like properties.
	 * @param string $name property name
	 * @return mixed property value
	 * @see getAttribute
	 */
	public function __get($name)
	{
		if (isset($this->_attributes[$name]) || array_key_exists($name, $this->_attributes)) {
			return $this->_attributes[$name];
		} elseif (isset($this->getTableSchema()->columns[$name])) {
			return null;
		} else {
			$t = strtolower($name);
			if (isset($this->_related[$t]) || $this->_related !== null && array_key_exists($t, $this->_related)) {
				return $this->_related[$t];
			}
			$value = parent::__get($name);
			if ($value instanceof ActiveRelation) {
				return $this->_related[$t] = $value->multiple ? $value->all() : $value->one();
			} else {
				return $value;
			}
		}
	}

	/**
	 * PHP setter magic method.
	 * This method is overridden so that AR attributes can be accessed like properties.
	 * @param string $name property name
	 * @param mixed $value property value
	 */
	public function __set($name, $value)
	{
		if (isset($this->getTableSchema()->columns[$name])) {
			$this->_attributes[$name] = $value;
		} else {
			parent::__set($name, $value);
		}
	}

	/**
	 * Checks if a property value is null.
	 * This method overrides the parent implementation by checking
	 * if the named attribute is null or not.
	 * @param string $name the property name or the event name
	 * @return boolean whether the property value is null
	 */
	public function __isset($name)
	{
		if (isset($this->getTableSchema()->columns[$name])) {
			return isset($this->_related[$name]);
		} else {
			$t = strtolower($name);
			if (isset($this->_related[$t])) {
				return true;
			}
			return parent::__isset($name);
		}
	}

	/**
	 * Sets a component property to be null.
	 * This method overrides the parent implementation by clearing
	 * the specified attribute value.
	 * @param string $name the property name or the event name
	 */
	public function __unset($name)
	{
		if (isset($this->getTableSchema()->columns[$name])) {
			unset($this->_attributes[$name]);
		} else {
			$t = strtolower($name);
			if (isset($this->_related[$t])) {
				unset($this->_related[$t]);
			} else {
				parent::__unset($name);
			}
		}
	}

	/**
	 * Declares a `has-one` relation.
	 * The declaration is returned in terms of an [[ActiveRelation]] instance
	 * through which the related record can be queried and retrieved back.
	 * @param string $class the class name of the related record
	 * @param array $link the primary-foreign key constraint. The keys of the array refer to
	 * the columns in the table associated with the `$class` model, while the values of the
	 * array refer to the corresponding columns in the table associated with this AR class.
	 * @param array $properties additional property values that should be used to
	 * initialize the newly created relation object.
	 * @return ActiveRelation the relation object.
	 */
	public function hasOne($class, $link, $properties = array())
	{
		if (strpos($class, '\\') === false) {
			$primaryClass = get_class($this);
			if (($pos = strrpos($primaryClass, '\\')) !== false) {
				$class = substr($primaryClass, 0, $pos + 1) . $class;
			}
		}

		$properties['modelClass'] = $class;
		$properties['primaryModel'] = $this;
		$properties['link'] = $link;
		$properties['multiple'] = false;
		return new ActiveRelation($properties);
	}

	/**
	 * Declares a `has-many` relation.
	 * The declaration is returned in terms of an [[ActiveRelation]] instance
	 * through which the related record can be queried and retrieved back.
	 * @param string $class the class name of the related record
	 * @param array $link the primary-foreign key constraint. The keys of the array refer to
	 * the columns in the table associated with the `$class` model, while the values of the
	 * array refer to the corresponding columns in the table associated with this AR class.
	 * @param array $properties additional property values that should be used to
	 * initialize the newly created relation object.
	 * @return ActiveRelation the relation object.
	 */
	public function hasMany($class, $link, $properties = array())
	{
		$relation = $this->hasOne($class, $link, $properties);
		$relation->multiple = true;
		return $relation;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function populateRelation($name, $value)
	{
		$this->_related[$name] = $value;
	}

	/**
	 * Returns the list of all attribute names of the model.
	 * The default implementation will return all column names of the table associated with this AR class.
	 * @return array list of attribute names.
	 */
	public function attributes()
	{
		return array_keys($this->getTableSchema()->columns);
	}

	/**
	 * Returns the named attribute value.
	 * If this record is the result of a query and the attribute is not loaded,
	 * null will be returned.
	 * @param string $name the attribute name
	 * @return mixed the attribute value. Null if the attribute is not set or does not exist.
	 * @see hasAttribute
	 */
	public function getAttribute($name)
	{
		return isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
	}

	/**
	 * Sets the named attribute value.
	 * @param string $name the attribute name
	 * @param mixed $value the attribute value.
	 * @see hasAttribute
	 */
	public function setAttribute($name, $value)
	{
		$this->_attributes[$name] = $value;
	}

	/**
	 * Returns the old value of the named attribute.
	 * If this record is the result of a query and the attribute is not loaded,
	 * null will be returned.
	 * @param string $name the attribute name
	 * @return mixed the old attribute value. Null if the attribute is not loaded before
	 * or does not exist.
	 * @see hasAttribute
	 */
	public function getOldAttribute($name)
	{
		return isset($this->_oldAttributes[$name]) ? $this->_oldAttributes[$name] : null;
	}

	/**
	 * Sets the old value of the named attribute.
	 * @param string $name the attribute name
	 * @param mixed $value the old attribute value.
	 * @see hasAttribute
	 */
	public function setOldAttribute($name, $value)
	{
		$this->_oldAttributes[$name] = $value;
	}

	/**
	 * Returns a value indicating whether the named attribute has been changed.
	 * @param string $name the name of the attribute
	 * @return boolean whether the attribute has been changed
	 */
	public function isAttributeChanged($name)
	{
		if (isset($this->_attribute[$name], $this->_oldAttributes[$name])) {
			return $this->_attribute[$name] !== $this->_oldAttributes[$name];
		} else {
			return isset($this->_attributes[$name]) || isset($this->_oldAttributes);
		}
	}

	/**
	 * Returns the attribute values that have been modified since they are loaded or saved most recently.
	 * @param string[]|null $names the names of the attributes whose values may be returned if they are
	 * changed recently. If null, [[attributes()]] will be used.
	 * @return array the changed attribute values (name-value pairs)
	 */
	public function getChangedAttributes($names = null)
	{
		if ($names === null) {
			$names = $this->attributes();
		}
		$names = array_flip($names);
		$attributes = array();
		if ($this->_oldAttributes === null) {
			foreach ($this->_attributes as $name => $value) {
				if (isset($names[$name])) {
					$attributes[$name] = $value;
				}
			}
		} else {
			foreach ($this->_attributes as $name => $value) {
				if (isset($names[$name]) && (!array_key_exists($name, $this->_oldAttributes) || $value !== $this->_oldAttributes[$name])) {
					$attributes[$name] = $value;
				}
			}
		}
		return $attributes;
	}

	/**
	 * Saves the current record.
	 *
	 * The record is inserted as a row into the database table if its [[isNewRecord]]
	 * property is true (usually the case when the record is created using the 'new'
	 * operator). Otherwise, it will be used to update the corresponding row in the table
	 * (usually the case if the record is obtained using one of those 'find' methods.)
	 *
	 * Validation will be performed before saving the record. If the validation fails,
	 * the record will not be saved. You can call [[getErrors()]] to retrieve the
	 * validation errors.
	 *
	 * If the record is saved via insertion, and if its primary key is auto-incremental
	 * and is not set before insertion, the primary key will be populated with the
	 * automatically generated key value.
	 *
	 * @param boolean $runValidation whether to perform validation before saving the record.
	 * If the validation fails, the record will not be saved to database.
	 * @param array $attributes list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved.
	 * @return boolean whether the saving succeeds
	 */
	public function save($runValidation = true, $attributes = null)
	{
		if (!$runValidation || $this->validate($attributes)) {
			return $this->getIsNewRecord() ? $this->insert($attributes) : $this->update($attributes);
		}
		return false;
	}

	/**
	 * Inserts a row into the table based on this active record attributes.
	 * If the table's primary key is auto-incremental and is null before insertion,
	 * it will be populated with the actual value after insertion.
	 * Note, validation is not performed in this method. You may call [[validate()]] to perform the validation.
	 * @param array $attributes list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved.
	 * @return boolean whether the attributes are valid and the record is inserted successfully.
	 * @throws Exception if the record is not new
	 */
	public function insert($attributes = null)
	{
		if ($this->beforeSave(true)) {
			$values = $this->getChangedAttributes($attributes);
			if ($values === array()) {
				foreach ($this->primaryKey() as $key) {
					$values[$key] = isset($this->_attributes[$key]) ? $this->_attributes[$key] : null;
				}
			}
			$db = $this->getDbConnection();
			$command = $db->createCommand()->insert($this->tableName(), $values);
			if ($command->execute()) {
				$table = $this->getTableSchema();
				if ($table->sequenceName !== null) {
					foreach ($table->primaryKey as $name) {
						if (!isset($this->_attributes[$name])) {
							$this->_oldAttributes[$name] = $this->_attributes[$name] = $db->getLastInsertID($table->sequenceName);
							break;
						}
					}
				}
				foreach ($values as $name => $value) {
					$this->_oldAttributes[$name] = $value;
				}
				$this->afterSave(true);
				return true;
			}
		}
		return false;
	}

	/**
	 * Updates the row represented by this active record.
	 * All loaded attributes will be saved to the database.
	 * Note, validation is not performed in this method. You may call [[validate()]] to perform the validation.
	 * @param array $attributes list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved.
	 * @return boolean whether the update is successful
	 */
	public function update($attributes = null)
	{
		if ($this->beforeSave(false)) {
			$values = $this->getChangedAttributes($attributes);
			if ($values !== array()) {
				$this->updateAll($values, $this->getOldPrimaryKey(true));
				foreach ($values as $name => $value) {
					$this->_oldAttributes[$name] = $this->_attributes[$name];
				}
				$this->afterSave(false);
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Saves one or several counter columns for the current AR object.
	 * Note that this method differs from [[updateAllCounters()]] in that it only
	 * saves counters for the current AR object.
	 *
	 * An example usage is as follows:
	 *
	 * ~~~
	 * $post = Post::find($id)->one();
	 * $post->updateCounters(array('view_count' => 1));
	 * ~~~
	 *
	 * Use negative values if you want to decrease the counters.
	 * @param array $counters the counters to be updated (attribute name => increment value)
	 * @return boolean whether the saving is successful
	 * @throws Exception if the record is new or any database error
	 * @see updateAllCounters()
	 */
	public function updateCounters($counters)
	{
		$this->updateAllCounters($counters, $this->getOldPrimaryKey(true));
		foreach ($counters as $name => $value) {
			$this->_attributes[$name] += $value;
			$this->_oldAttributes[$name] = $this->_attributes[$name];
		}
		return true;
	}

	/**
	 * Deletes the row corresponding to this active record.
	 * @return boolean whether the deletion is successful.
	 */
	public function delete()
	{
		if ($this->beforeDelete()) {
			$result = $this->deleteAll($this->getPrimaryKey(true)) > 0;
			$this->_oldAttributes = null;
			$this->afterDelete();
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Returns if the current record is new.
	 * @return boolean whether the record is new and should be inserted when calling [[save()]].
	 */
	public function getIsNewRecord()
	{
		return $this->_oldAttributes === null;
	}

	/**
	 * Sets if the record is new.
	 * @param boolean $value whether the record is new and should be inserted when calling [[save()]].
	 * @see getIsNewRecord
	 */
	public function setIsNewRecord($value)
	{
		$this->_oldAttributes = $value ? null : $this->_attributes;
	}

	public function beforeSave($insert)
	{
		$event = new ModelEvent($this);
		$this->trigger($insert ? 'beforeInsert' : 'beforeUpdate', $event);
		return $event->isValid;
	}

	public function afterSave($insert)
	{
		$this->trigger($insert ? 'afterInsert' : 'afterUpdate');
	}

	/**
	 * This method is invoked before deleting a record.
	 * The default implementation raises the `beforeDelete` event.
	 * You may override this method to do any preparation work for record deletion.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 * @return boolean whether the record should be deleted. Defaults to true.
	 */
	public function beforeDelete()
	{
		$event = new ModelEvent($this);
		$this->trigger('beforeDelete', $event);
		return $event->isValid;
	}

	/**
	 * This method is invoked after deleting a record.
	 * The default implementation raises the `afterDelete` event.
	 * You may override this method to do postprocessing after the record is deleted.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 */
	public function afterDelete()
	{
		$this->trigger('afterDelete');
	}

	/**
	 * Repopulates this active record with the latest data.
	 * @param array $attributes
	 * @return boolean whether the row still exists in the database. If true, the latest data will be populated to this active record.
	 */
	public function refresh($attributes = null)
	{
		$record = $this->find($this->getPrimaryKey(true));
		if ($record === null) {
			return false;
		}
		if ($attributes === null) {
			foreach ($this->attributes() as $name) {
				$this->_attributes[$name] = $record->_attributes[$name];
			}
			$this->_oldAttributes = $this->_attributes;
		} else {
			foreach ($attributes as $name) {
				$this->_oldAttributes[$name] = $this->_attributes[$name] = $record->_attributes[$name];
			}
		}
		return true;
	}

	/**
	 * Compares current active record with another one.
	 * The comparison is made by comparing table name and the primary key values of the two active records.
	 * @param ActiveRecord $record record to compare to
	 * @return boolean whether the two active records refer to the same row in the database table.
	 */
	public function equals($record)
	{
		return $this->tableName() === $record->tableName() && $this->getPrimaryKey() === $record->getPrimaryKey();
	}

	/**
	 * Returns the primary key value.
	 * @param boolean $asArray whether to return the primary key value as an array. If true,
	 * the return value will be an array with column name as key and column value as value.
	 * Note that for composite primary keys, an array will always be returned regardless of this parameter value.
	 * @return mixed the primary key value. An array (column name=>column value) is returned if the primary key is composite.
	 * If primary key is not defined, null will be returned.
	 */
	public function getPrimaryKey($asArray = false)
	{
		$keys = $this->primaryKey();
		if (count($keys) === 1 && !$asArray) {
			return isset($this->_attributes[$keys[0]]) ? $this->_attributes[$keys[0]] : null;
		} else {
			$values = array();
			foreach ($keys as $name) {
				$values[$name] = isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
			}
			return $values;
		}
	}

	/**
	 * Returns the old primary key value.
	 * This refers to the primary key value that is populated into the record
	 * after executing a find method (e.g. find(), findAll()).
	 * The value remains unchanged even if the primary key attribute is manually assigned with a different value.
	 * @param boolean $asArray whether to return the primary key value as an array. If true,
	 * the return value will be an array with column name as key and column value as value.
	 * If this is false (default), a scalar value will be returned for non-composite primary key.
	 * @return string|array the old primary key value. An array (column name=>column value) is returned if the primary key is composite.
	 * If primary key is not defined, null will be returned.
	 */
	public function getOldPrimaryKey($asArray = false)
	{
		$keys = $this->primaryKey();
		if (count($keys) === 1 && !$asArray) {
			return isset($this->_oldAttributes[$keys[0]]) ? $this->_oldAttributes[$keys[0]] : null;
		} else {
			$values = array();
			foreach ($keys as $name) {
				$values[$name] = isset($this->_oldAttributes[$name]) ? $this->_oldAttributes[$name] : null;
			}
			return $values;
		}
	}

	/**
	 * Creates an active record with the given attributes.
	 * This method is called by [[ActiveQuery]] to populate the query results
	 * into Active Records.
	 * @param array $row attribute values (name => value)
	 * @return ActiveRecord the newly created active record.
	 */
	public static function create($row)
	{
		$record = static::instantiate($row);
		$columns = static::getTableSchema()->columns;
		foreach ($row as $name => $value) {
			if (isset($columns[$name])) {
				$record->_attributes[$name] = $value;
			} else {
				$record->$name = $value;
			}
		}
		$record->_oldAttributes = $record->_attributes;
		return $record;
	}

	/**
	 * Creates an active record instance.
	 * This method is called by [[create()]].
	 * You may override this method if the instance being created
	 * depends the attributes that are to be populated to the record.
	 * For example, by creating a record based on the value of a column,
	 * you may implement the so-called single-table inheritance mapping.
	 * @param array $row list of attribute values for the active records.
	 * @return ActiveRecord the active record
	 */
	public static function instantiate($row)
	{
		return new static;
	}

	/**
	 * Returns whether there is an element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed $offset the offset to check on
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return $this->__isset($offset);
	}

	/**
	 * Returns the relation object with the specified name.
	 * A relation is defined by a getter method which returns an [[ActiveRelation]] object.
	 * It can be declared in either the Active Record class itself or one of its behaviors.
	 * @param string $name the relation name
	 * @return ActiveRelation the relation object
	 * @throws BadParamException if the named relation does not exist.
	 */
	public function getRelation($name)
	{
		$getter = 'get' . $name;
		try {
			$relation = $this->$getter();
			if ($relation instanceof ActiveRelation) {
				return $relation;
			}
		} catch (BadMethodException $e) {
		}
		throw new BadParamException(get_class($this) . ' has no relation named "' . $name . '".');
	}

	/**
	 * @param string $name
	 * @param ActiveRecord $model
	 * @param array $extraColumns
	 */
	public function link($name, $model, $extraColumns = array())
	{
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
			$this->getDbConnection()->createCommand()
				->insert($viaTable, $columns)->execute();
		} else {
			$p1 = $model->isPrimaryKey(array_keys($relation->link));
			$p2 = $this->isPrimaryKey(array_values($relation->link));
			if ($p1 && $p2) {
				if ($this->getIsNewRecord() && $model->getIsNewRecord()) {
					throw new Exception('both new');
				} elseif ($this->getIsNewRecord()) {
					$this->bindModels(array_flip($relation->link), $this, $model);
				} elseif ($model->getIsNewRecord()) {
					$this->bindModels($relation->link, $model, $this);
				} else {
					throw new Exception('both old');
				}
			} elseif ($p1) {
				$this->bindModels(array_flip($relation->link), $this, $model);
			} elseif ($p2) {
				$this->bindModels($relation->link, $model, $this);
			} else {
				throw new Exception('');
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
	 * @param string $name
	 * @param ActiveRecord $model
	 * @param boolean $delete whether to delete the model that contains the foreign key.
	 * If false, the model's foreign key will be set null and saved.
	 * @throws Exception
	 */
	public function unlink($name, $model, $delete = true)
	{
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
			$command = $this->getDbConnection()->createCommand();
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
				throw new Exception('');
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

	/**
	 * @param array $link
	 * @param ActiveRecord $foreignModel
	 * @param ActiveRecord $primaryModel
	 * @throws Exception
	 */
	private function bindModels($link, $foreignModel, $primaryModel)
	{
		foreach ($link as $fk => $pk) {
			$value = $primaryModel->$pk;
			if ($value === null) {
				throw new Exception('Primary Key is null');
			}
			$foreignModel->$fk = $value;
		}
		$foreignModel->save(false);
	}

	/**
	 * @param array $keys
	 * @return boolean
	 */
	private function isPrimaryKey($keys)
	{
		$pks = $this->primaryKey();
		foreach ($keys as $key) {
			if (!in_array($key, $pks, true)) {
				return false;
			}
		}
		return true;
	}
}
