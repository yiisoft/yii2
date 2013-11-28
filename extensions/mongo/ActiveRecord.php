<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongo;

use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\base\ModelEvent;
use yii\base\UnknownMethodException;
use yii\db\ActiveRelationInterface;
use yii\db\StaleObjectException;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * Class ActiveRecord
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
abstract class ActiveRecord extends Model
{
	/**
	 * @event Event an event that is triggered when the record is initialized via [[init()]].
	 */
	const EVENT_INIT = 'init';
	/**
	 * @event Event an event that is triggered after the record is created and populated with query result.
	 */
	const EVENT_AFTER_FIND = 'afterFind';
	/**
	 * @event ModelEvent an event that is triggered before inserting a record.
	 * You may set [[ModelEvent::isValid]] to be false to stop the insertion.
	 */
	const EVENT_BEFORE_INSERT = 'beforeInsert';
	/**
	 * @event Event an event that is triggered after a record is inserted.
	 */
	const EVENT_AFTER_INSERT = 'afterInsert';
	/**
	 * @event ModelEvent an event that is triggered before updating a record.
	 * You may set [[ModelEvent::isValid]] to be false to stop the update.
	 */
	const EVENT_BEFORE_UPDATE = 'beforeUpdate';
	/**
	 * @event Event an event that is triggered after a record is updated.
	 */
	const EVENT_AFTER_UPDATE = 'afterUpdate';
	/**
	 * @event ModelEvent an event that is triggered before deleting a record.
	 * You may set [[ModelEvent::isValid]] to be false to stop the deletion.
	 */
	const EVENT_BEFORE_DELETE = 'beforeDelete';
	/**
	 * @event Event an event that is triggered after a record is deleted.
	 */
	const EVENT_AFTER_DELETE = 'afterDelete';

	/**
	 * @var array attribute values indexed by attribute names
	 */
	private $_attributes = [];
	/**
	 * @var array old attribute values indexed by attribute names.
	 */
	private $_oldAttributes;
	/**
	 * @var array related models indexed by the relation names
	 */
	private $_related = [];

	/**
	 * Returns the database connection used by this AR class.
	 * By default, the "db" application component is used as the database connection.
	 * You may override this method if you want to use a different database connection.
	 * @return Connection the database connection used by this AR class.
	 */
	public static function getDb()
	{
		return \Yii::$app->getComponent('mongo');
	}

	/**
	 * Creates an [[ActiveQuery]] instance for query purpose.
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
	 * @throws InvalidConfigException if the AR class does not have a primary key
	 * @see createQuery()
	 */
	public static function find($q = null)
	{
		$query = static::createQuery();
		if (is_array($q)) {
			return $query->where($q)->one();
		} elseif ($q !== null) {
			// query by primary key
			$primaryKey = static::primaryKey();
			if (isset($primaryKey[0])) {
				return $query->where([$primaryKey[0] => $q])->one();
			} else {
				throw new InvalidConfigException(get_called_class() . ' must have a primary key.');
			}
		}
		return $query;
	}

	/**
	 * Updates the whole table using the provided attribute values and conditions.
	 * For example, to change the status to be 1 for all customers whose status is 2:
	 *
	 * ~~~
	 * Customer::updateAll(['status' => 1], ['status' = 2]);
	 * ~~~
	 *
	 * @param array $attributes attribute values (name-value pairs) to be saved into the table
	 * @param array $condition the conditions that will be put in the WHERE part of the UPDATE SQL.
	 * Please refer to [[Query::where()]] on how to specify this parameter.
	 * @param array $options list of options in format: optionName => optionValue.
	 * @return integer the number of rows updated.
	 */
	public static function updateAll($attributes, $condition = [], $options = [])
	{
		$options['w'] = 1;
		if (!array_key_exists('multiple', $options)) {
			$options['multiple'] = true;
		}
		return static::getCollection()->update($condition, $attributes, $options);
	}

	/**
	 * Updates the whole table using the provided counter changes and conditions.
	 * For example, to increment all customers' age by 1,
	 *
	 * ~~~
	 * Customer::updateAllCounters(['age' => 1]);
	 * ~~~
	 *
	 * @param array $counters the counters to be updated (attribute name => increment value).
	 * Use negative values if you want to decrement the counters.
	 * @param array $condition the conditions that will be put in the WHERE part of the UPDATE SQL.
	 * Please refer to [[Query::where()]] on how to specify this parameter.
	 * @param array $options list of options in format: optionName => optionValue.
	 * @return integer the number of rows updated.
	 */
	public static function updateAllCounters($counters, $condition = [], $options = [])
	{
		$options['w'] = 1;
		if (!array_key_exists('multiple', $options)) {
			$options['multiple'] = true;
		}
		$data = [];
		foreach ($counters as $name => $value) {
			$data[$name]['$inc'] = $value;
		}
		return static::getCollection()->update($condition, $data, $options);
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
	 * Please refer to [[Query::where()]] on how to specify this parameter.
	 * @param array $options list of options in format: optionName => optionValue.
	 * @return integer the number of rows updated.
	 */
	public static function deleteAll($condition = [], $options = [])
	{
		$options['w'] = 1;
		if (!array_key_exists('multiple', $options)) {
			$options['multiple'] = true;
		}
		return static::getCollection()->remove($condition, $options);
	}

	/**
	 * Creates an [[ActiveQuery]] instance.
	 * This method is called by [[find()]] to start a SELECT query.
	 * You may override this method to return a customized query (e.g. `CustomerQuery` specified
	 * written for querying `Customer` purpose.)
	 * @return ActiveQuery the newly created [[ActiveQuery]] instance.
	 */
	public static function createQuery()
	{
		return new ActiveQuery(['modelClass' => get_called_class()]);
	}

	/**
	 * Declares the name of the Mongo collection associated with this AR class.
	 * Collection name can be either a string or array:
	 *  - if string considered as the name of the collection inside the default database.
	 *  - if array - first element considered as the name of the database, second - as
	 * name of collection inside that database
	 * By default this method returns the class name as the collection name by calling [[Inflector::camel2id()]].
	 * For example, 'Customer' becomes 'customer', and 'OrderItem' becomes
	 * 'order_item'. You may override this method if the table is not named after this convention.
	 * @return string the table name
	 */
	public static function collectionName()
	{
		return Inflector::camel2id(StringHelper::basename(get_called_class()), '_');
	}

	/**
	 * Return the Mongo collection instance for this AR class.
	 * @return Collection collection instance.
	 */
	public static function getCollection()
	{
		return static::getDb()->getCollection(static::collectionName());
	}

	/**
	 * Returns the primary key name(s) for this AR class.
	 * The default implementation will return ['_id'].
	 *
	 * Note that an array should be returned even for a table with single primary key.
	 *
	 * @return string[] the primary keys of the associated database table.
	 */
	public static function primaryKey()
	{
		return ['_id'];
	}

	/**
	 * Returns the name of the column that stores the lock version for implementing optimistic locking.
	 *
	 * Optimistic locking allows multiple users to access the same record for edits and avoids
	 * potential conflicts. In case when a user attempts to save the record upon some staled data
	 * (because another user has modified the data), a [[StaleObjectException]] exception will be thrown,
	 * and the update or deletion is skipped.
	 *
	 * Optimistic locking is only supported by [[update()]] and [[delete()]].
	 *
	 * To use Optimistic locking:
	 *
	 * 1. Create a column to store the version number of each row. The column type should be `BIGINT DEFAULT 0`.
	 *    Override this method to return the name of this column.
	 * 2. In the Web form that collects the user input, add a hidden field that stores
	 *    the lock version of the recording being updated.
	 * 3. In the controller action that does the data updating, try to catch the [[StaleObjectException]]
	 *    and implement necessary business logic (e.g. merging the changes, prompting stated data)
	 *    to resolve the conflict.
	 *
	 * @return string the column name that stores the lock version of a table row.
	 * If null is returned (default implemented), optimistic locking will not be supported.
	 */
	public function optimisticLock()
	{
		return null;
	}

	/**
	 * PHP getter magic method.
	 * This method is overridden so that attributes and related objects can be accessed like properties.
	 * @param string $name property name
	 * @return mixed property value
	 * @see getAttribute()
	 */
	public function __get($name)
	{
		if (isset($this->_attributes[$name]) || array_key_exists($name, $this->_attributes)) {
			return $this->_attributes[$name];
		} elseif ($this->hasAttribute($name)) {
			return null;
		} else {
			if (isset($this->_related[$name]) || array_key_exists($name, $this->_related)) {
				return $this->_related[$name];
			}
			$value = parent::__get($name);
			if ($value instanceof ActiveRelationInterface) {
				return $this->_related[$name] = $value->multiple ? $value->all() : $value->one();
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
		if ($this->hasAttribute($name)) {
			$this->_attributes[$name] = $value;
		} else {
			parent::__set($name, $value);
		}
	}

	/**
	 * Checks if a property value is null.
	 * This method overrides the parent implementation by checking if the named attribute is null or not.
	 * @param string $name the property name or the event name
	 * @return boolean whether the property value is null
	 */
	public function __isset($name)
	{
		try {
			return $this->__get($name) !== null;
		} catch (\Exception $e) {
			return false;
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
		if ($this->hasAttribute($name)) {
			unset($this->_attributes[$name]);
		} else {
			if (isset($this->_related[$name])) {
				unset($this->_related[$name]);
			} else {
				parent::__unset($name);
			}
		}
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
	 *     return $this->hasOne(Country::className(), ['id' => 'country_id']);
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
	 * the attributes of the record associated with the `$class` model, while the values of the
	 * array refer to the corresponding attributes in **this** AR class.
	 * @return ActiveRelationInterface the relation object.
	 */
	public function hasOne($class, $link)
	{
		/** @var ActiveRecord $class */
		return $class::createActiveRelation([
			'modelClass' => $class,
			'primaryModel' => $this,
			'link' => $link,
			'multiple' => false,
		]);
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
	 *     return $this->hasMany(Order::className(), ['customer_id' => 'id']);
	 * }
	 * ~~~
	 *
	 * Note that in the above, the 'customer_id' key in the `$link` parameter refers to
	 * an attribute name in the related class `Order`, while the 'id' value refers to
	 * an attribute name in the current AR class.
	 *
	 * @param string $class the class name of the related record
	 * @param array $link the primary-foreign key constraint. The keys of the array refer to
	 * the attributes of the record associated with the `$class` model, while the values of the
	 * array refer to the corresponding attributes in **this** AR class.
	 * @return ActiveRelationInterface the relation object.
	 */
	public function hasMany($class, $link)
	{
		/** @var ActiveRecord $class */
		return $class::createActiveRelation([
			'modelClass' => $class,
			'primaryModel' => $this,
			'link' => $link,
			'multiple' => true,
		]);
	}

	/**
	 * Creates an [[ActiveRelation]] instance.
	 * This method is called by [[hasOne()]] and [[hasMany()]] to create a relation instance.
	 * You may override this method to return a customized relation.
	 * @param array $config the configuration passed to the ActiveRelation class.
	 * @return ActiveRelation the newly created [[ActiveRelation]] instance.
	 */
	public static function createActiveRelation($config = [])
	{
		return new ActiveRelation($config);
	}

	/**
	 * Populates the named relation with the related records.
	 * Note that this method does not check if the relation exists or not.
	 * @param string $name the relation name (case-sensitive)
	 * @param ActiveRecord|array|null the related records to be populated into the relation.
	 */
	public function populateRelation($name, $records)
	{
		$this->_related[$name] = $records;
	}

	/**
	 * Check whether the named relation has been populated with records.
	 * @param string $name the relation name (case-sensitive)
	 * @return bool whether relation has been populated with records.
	 */
	public function isRelationPopulated($name)
	{
		return array_key_exists($name, $this->_related);
	}

	/**
	 * Returns all populated relations.
	 * @return array an array of relation data indexed by relation names.
	 */
	public function getPopulatedRelations()
	{
		return $this->_related;
	}

	/**
	 * Returns the list of all attribute names of the model.
	 * The default implementation will return all column names of the table associated with this AR class.
	 * @return array list of attribute names.
	 */
	public function attributes()
	{
		// TODO: declare attributes
		return [];
	}

	/**
	 * Returns a value indicating whether the model has an attribute with the specified name.
	 * @param string $name the name of the attribute
	 * @return boolean whether the model has an attribute with the specified name.
	 */
	public function hasAttribute($name)
	{
		return isset($this->_attributes[$name]) || in_array($name, $this->attributes());
	}

	/**
	 * Returns the named attribute value.
	 * If this record is the result of a query and the attribute is not loaded,
	 * null will be returned.
	 * @param string $name the attribute name
	 * @return mixed the attribute value. Null if the attribute is not set or does not exist.
	 * @see hasAttribute()
	 */
	public function getAttribute($name)
	{
		return isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
	}

	/**
	 * Sets the named attribute value.
	 * @param string $name the attribute name
	 * @param mixed $value the attribute value.
	 * @throws InvalidParamException if the named attribute does not exist.
	 * @see hasAttribute()
	 */
	public function setAttribute($name, $value)
	{
		if ($this->hasAttribute($name)) {
			$this->_attributes[$name] = $value;
		} else {
			throw new InvalidParamException(get_class($this) . ' has no attribute named "' . $name . '".');
		}
	}

	/**
	 * Returns the old attribute values.
	 * @return array the old attribute values (name-value pairs)
	 */
	public function getOldAttributes()
	{
		return $this->_oldAttributes === null ? [] : $this->_oldAttributes;
	}

	/**
	 * Sets the old attribute values.
	 * All existing old attribute values will be discarded.
	 * @param array $values old attribute values to be set.
	 */
	public function setOldAttributes($values)
	{
		$this->_oldAttributes = $values;
	}

	/**
	 * Returns the old value of the named attribute.
	 * If this record is the result of a query and the attribute is not loaded,
	 * null will be returned.
	 * @param string $name the attribute name
	 * @return mixed the old attribute value. Null if the attribute is not loaded before
	 * or does not exist.
	 * @see hasAttribute()
	 */
	public function getOldAttribute($name)
	{
		return isset($this->_oldAttributes[$name]) ? $this->_oldAttributes[$name] : null;
	}

	/**
	 * Sets the old value of the named attribute.
	 * @param string $name the attribute name
	 * @param mixed $value the old attribute value.
	 * @throws InvalidParamException if the named attribute does not exist.
	 * @see hasAttribute()
	 */
	public function setOldAttribute($name, $value)
	{
		if (isset($this->_oldAttributes[$name]) || $this->hasAttribute($name)) {
			$this->_oldAttributes[$name] = $value;
		} else {
			throw new InvalidParamException(get_class($this) . ' has no attribute named "' . $name . '".');
		}
	}

	/**
	 * Returns a value indicating whether the named attribute has been changed.
	 * @param string $name the name of the attribute
	 * @return boolean whether the attribute has been changed
	 */
	public function isAttributeChanged($name)
	{
		if (isset($this->_attributes[$name], $this->_oldAttributes[$name])) {
			return $this->_attributes[$name] !== $this->_oldAttributes[$name];
		} else {
			return isset($this->_attributes[$name]) || isset($this->_oldAttributes[$name]);
		}
	}

	/**
	 * Returns the attribute values that have been modified since they are loaded or saved most recently.
	 * @param string[]|null $names the names of the attributes whose values may be returned if they are
	 * changed recently. If null, [[attributes()]] will be used.
	 * @return array the changed attribute values (name-value pairs)
	 */
	public function getDirtyAttributes($names = null)
	{
		if ($names === null) {
			$names = $this->attributes();
		}
		$names = array_flip($names);
		$attributes = [];
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
	 * This method will call [[insert()]] when [[isNewRecord]] is true, or [[update()]]
	 * when [[isNewRecord]] is false.
	 *
	 * For example, to save a customer record:
	 *
	 * ~~~
	 * $customer = new Customer;  // or $customer = Customer::find($id);
	 * $customer->name = $name;
	 * $customer->email = $email;
	 * $customer->save();
	 * ~~~
	 *
	 *
	 * @param boolean $runValidation whether to perform validation before saving the record.
	 * If the validation fails, the record will not be saved to database.
	 * @param array $attributes list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved.
	 * @return boolean whether the saving succeeds
	 */
	public function save($runValidation = true, $attributes = null)
	{
		if ($this->getIsNewRecord()) {
			return $this->insert($runValidation, $attributes);
		} else {
			return $this->update($runValidation, $attributes) !== false;
		}
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
	 * Only the [[dirtyAttributes|changed attribute values]] will be inserted into database.
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
	 * @throws \Exception in case insert failed.
	 */
	public function insert($runValidation = true, $attributes = null)
	{
		if ($runValidation && !$this->validate($attributes)) {
			return false;
		}
		$result = $this->insertInternal($attributes);
		return $result;
	}

	/**
	 * @see ActiveRecord::insert()
	 */
	private function insertInternal($attributes = null)
	{
		if (!$this->beforeSave(true)) {
			return false;
		}
		$values = $this->getDirtyAttributes($attributes);
		if (empty($values)) {
			foreach ($this->primaryKey() as $key) {
				$values[$key] = isset($this->_attributes[$key]) ? $this->_attributes[$key] : null;
			}
		}
		$collection = static::getCollection();
		$newId = $collection->insert($values);
		$this->setAttribute('_id', $newId);
		foreach ($values as $name => $value) {
			$this->_oldAttributes[$name] = $value;
		}
		$this->afterSave(true);
		return true;
	}

	/**
	 * Saves the changes to this active record into the associated database table.
	 *
	 * This method performs the following steps in order:
	 *
	 * 1. call [[beforeValidate()]] when `$runValidation` is true. If validation
	 *    fails, it will skip the rest of the steps;
	 * 2. call [[afterValidate()]] when `$runValidation` is true.
	 * 3. call [[beforeSave()]]. If the method returns false, it will skip the
	 *    rest of the steps;
	 * 4. save the record into database. If this fails, it will skip the rest of the steps;
	 * 5. call [[afterSave()]];
	 *
	 * In the above step 1, 2, 3 and 5, events [[EVENT_BEFORE_VALIDATE]],
	 * [[EVENT_BEFORE_UPDATE]], [[EVENT_AFTER_UPDATE]] and [[EVENT_AFTER_VALIDATE]]
	 * will be raised by the corresponding methods.
	 *
	 * Only the [[changedAttributes|changed attribute values]] will be saved into database.
	 *
	 * For example, to update a customer record:
	 *
	 * ~~~
	 * $customer = Customer::find($id);
	 * $customer->name = $name;
	 * $customer->email = $email;
	 * $customer->update();
	 * ~~~
	 *
	 * Note that it is possible the update does not affect any row in the table.
	 * In this case, this method will return 0. For this reason, you should use the following
	 * code to check if update() is successful or not:
	 *
	 * ~~~
	 * if ($this->update() !== false) {
	 *     // update successful
	 * } else {
	 *     // update failed
	 * }
	 * ~~~
	 *
	 * @param boolean $runValidation whether to perform validation before saving the record.
	 * If the validation fails, the record will not be inserted into the database.
	 * @param array $attributes list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved.
	 * @return integer|boolean the number of rows affected, or false if validation fails
	 * or [[beforeSave()]] stops the updating process.
	 * @throws StaleObjectException if [[optimisticLock|optimistic locking]] is enabled and the data
	 * being updated is outdated.
	 * @throws \Exception in case update failed.
	 */
	public function update($runValidation = true, $attributes = null)
	{
		if ($runValidation && !$this->validate($attributes)) {
			return false;
		}
		$result = $this->updateInternal($attributes);
		return $result;
	}

	/**
	 * @see CActiveRecord::update()
	 * @throws StaleObjectException
	 */
	private function updateInternal($attributes = null)
	{
		if (!$this->beforeSave(false)) {
			return false;
		}
		$values = $this->getDirtyAttributes($attributes);
		if (empty($values)) {
			$this->afterSave(false);
			return 0;
		}
		$condition = $this->getOldPrimaryKey(true);
		$lock = $this->optimisticLock();
		if ($lock !== null) {
			if (!isset($values[$lock])) {
				$values[$lock] = $this->$lock + 1;
			}
			$condition[$lock] = $this->$lock;
		}
		// We do not check the return value of update() because it's possible
		// that it doesn't change anything and thus returns 0.
		$rows = static::getCollection()->update($condition, $values, ['w' => 1]);

		if ($lock !== null && !$rows) {
			throw new StaleObjectException('The object being updated is outdated.');
		}

		foreach ($values as $name => $value) {
			$this->_oldAttributes[$name] = $this->_attributes[$name];
		}
		$this->afterSave(false);
		return $rows;
	}

	/**
	 * Updates one or several counter columns for the current AR object.
	 * Note that this method differs from [[updateAllCounters()]] in that it only
	 * saves counters for the current AR object.
	 *
	 * An example usage is as follows:
	 *
	 * ~~~
	 * $post = Post::find($id);
	 * $post->updateCounters(['view_count' => 1]);
	 * ~~~
	 *
	 * @param array $counters the counters to be updated (attribute name => increment value)
	 * Use negative values if you want to decrement the counters.
	 * @return boolean whether the saving is successful
	 * @see updateAllCounters()
	 */
	public function updateCounters($counters)
	{
		if ($this->updateAllCounters($counters, $this->getOldPrimaryKey(true)) > 0) {
			foreach ($counters as $name => $value) {
				$this->_attributes[$name] += $value;
				$this->_oldAttributes[$name] = $this->_attributes[$name];
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Deletes the table row corresponding to this active record.
	 *
	 * This method performs the following steps in order:
	 *
	 * 1. call [[beforeDelete()]]. If the method returns false, it will skip the
	 *    rest of the steps;
	 * 2. delete the record from the database;
	 * 3. call [[afterDelete()]].
	 *
	 * In the above step 1 and 3, events named [[EVENT_BEFORE_DELETE]] and [[EVENT_AFTER_DELETE]]
	 * will be raised by the corresponding methods.
	 *
	 * @return integer|boolean the number of rows deleted, or false if the deletion is unsuccessful for some reason.
	 * Note that it is possible the number of rows deleted is 0, even though the deletion execution is successful.
	 * @throws StaleObjectException if [[optimisticLock|optimistic locking]] is enabled and the data
	 * being deleted is outdated.
	 * @throws \Exception in case delete failed.
	 */
	public function delete()
	{
		$result = false;
		if ($this->beforeDelete()) {
			// we do not check the return value of deleteAll() because it's possible
			// the record is already deleted in the database and thus the method will return 0
			$condition = $this->getOldPrimaryKey(true);
			$lock = $this->optimisticLock();
			if ($lock !== null) {
				$condition[$lock] = $this->$lock;
			}
			$result = static::getCollection()->remove($condition, ['w' => 1]);
			if ($lock !== null && !$result) {
				throw new StaleObjectException('The object being deleted is outdated.');
			}
			$this->_oldAttributes = null;
			$this->afterDelete();
		}
		return $result;
	}

	/**
	 * Returns a value indicating whether the current record is new.
	 * @return boolean whether the record is new and should be inserted when calling [[save()]].
	 */
	public function getIsNewRecord()
	{
		return $this->_oldAttributes === null;
	}

	/**
	 * Sets the value indicating whether the record is new.
	 * @param boolean $value whether the record is new and should be inserted when calling [[save()]].
	 * @see getIsNewRecord()
	 */
	public function setIsNewRecord($value)
	{
		$this->_oldAttributes = $value ? null : $this->_attributes;
	}

	/**
	 * Initializes the object.
	 * This method is called at the end of the constructor.
	 * The default implementation will trigger an [[EVENT_INIT]] event.
	 * If you override this method, make sure you call the parent implementation at the end
	 * to ensure triggering of the event.
	 */
	public function init()
	{
		parent::init();
		$this->trigger(self::EVENT_INIT);
	}

	/**
	 * This method is called when the AR object is created and populated with the query result.
	 * The default implementation will trigger an [[EVENT_AFTER_FIND]] event.
	 * When overriding this method, make sure you call the parent implementation to ensure the
	 * event is triggered.
	 */
	public function afterFind()
	{
		$this->trigger(self::EVENT_AFTER_FIND);
	}

	/**
	 * This method is called at the beginning of inserting or updating a record.
	 * The default implementation will trigger an [[EVENT_BEFORE_INSERT]] event when `$insert` is true,
	 * or an [[EVENT_BEFORE_UPDATE]] event if `$insert` is false.
	 * When overriding this method, make sure you call the parent implementation like the following:
	 *
	 * ~~~
	 * public function beforeSave($insert)
	 * {
	 *     if (parent::beforeSave($insert)) {
	 *         // ...custom code here...
	 *         return true;
	 *     } else {
	 *         return false;
	 *     }
	 * }
	 * ~~~
	 *
	 * @param boolean $insert whether this method called while inserting a record.
	 * If false, it means the method is called while updating a record.
	 * @return boolean whether the insertion or updating should continue.
	 * If false, the insertion or updating will be cancelled.
	 */
	public function beforeSave($insert)
	{
		$event = new ModelEvent;
		$this->trigger($insert ? self::EVENT_BEFORE_INSERT : self::EVENT_BEFORE_UPDATE, $event);
		return $event->isValid;
	}

	/**
	 * This method is called at the end of inserting or updating a record.
	 * The default implementation will trigger an [[EVENT_AFTER_INSERT]] event when `$insert` is true,
	 * or an [[EVENT_AFTER_UPDATE]] event if `$insert` is false.
	 * When overriding this method, make sure you call the parent implementation so that
	 * the event is triggered.
	 * @param boolean $insert whether this method called while inserting a record.
	 * If false, it means the method is called while updating a record.
	 */
	public function afterSave($insert)
	{
		$this->trigger($insert ? self::EVENT_AFTER_INSERT : self::EVENT_AFTER_UPDATE);
	}

	/**
	 * This method is invoked before deleting a record.
	 * The default implementation raises the [[EVENT_BEFORE_DELETE]] event.
	 * When overriding this method, make sure you call the parent implementation like the following:
	 *
	 * ~~~
	 * public function beforeDelete()
	 * {
	 *     if (parent::beforeDelete()) {
	 *         // ...custom code here...
	 *         return true;
	 *     } else {
	 *         return false;
	 *     }
	 * }
	 * ~~~
	 *
	 * @return boolean whether the record should be deleted. Defaults to true.
	 */
	public function beforeDelete()
	{
		$event = new ModelEvent;
		$this->trigger(self::EVENT_BEFORE_DELETE, $event);
		return $event->isValid;
	}

	/**
	 * This method is invoked after deleting a record.
	 * The default implementation raises the [[EVENT_AFTER_DELETE]] event.
	 * You may override this method to do postprocessing after the record is deleted.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 */
	public function afterDelete()
	{
		$this->trigger(self::EVENT_AFTER_DELETE);
	}

	/**
	 * Repopulates this active record with the latest data.
	 * @return boolean whether the row still exists in the database. If true, the latest data
	 * will be populated to this active record. Otherwise, this record will remain unchanged.
	 */
	public function refresh()
	{
		$record = $this->find($this->getPrimaryKey(true));
		if ($record === null) {
			return false;
		}
		foreach ($this->attributes() as $name) {
			$this->_attributes[$name] = isset($record->_attributes[$name]) ? $record->_attributes[$name] : null;
		}
		$this->_oldAttributes = $this->_attributes;
		$this->_related = [];
		return true;
	}

	/**
	 * Returns a value indicating whether the given active record is the same as the current one.
	 * The comparison is made by comparing the table names and the primary key values of the two active records.
	 * If one of the records [[isNewRecord|is new]] they are also considered not equal.
	 * @param ActiveRecord $record record to compare to
	 * @return boolean whether the two active records refer to the same row in the same database table.
	 */
	public function equals($record)
	{
		if ($this->isNewRecord || $record->isNewRecord) {
			return false;
		}
		return $this->collectionName() === $record->collectionName() && $this->getPrimaryKey() === $record->getPrimaryKey();
	}

	/**
	 * Returns the primary key value(s).
	 * @param boolean $asArray whether to return the primary key value as an array. If true,
	 * the return value will be an array with column names as keys and column values as values.
	 * Note that for composite primary keys, an array will always be returned regardless of this parameter value.
	 * @property mixed The primary key value. An array (column name => column value) is returned if
	 * the primary key is composite. A string is returned otherwise (null will be returned if
	 * the key value is null).
	 * @return mixed the primary key value. An array (column name => column value) is returned if the primary key
	 * is composite or `$asArray` is true. A string is returned otherwise (null will be returned if
	 * the key value is null).
	 */
	public function getPrimaryKey($asArray = false)
	{
		$keys = $this->primaryKey();
		if (count($keys) === 1 && !$asArray) {
			return isset($this->_attributes[$keys[0]]) ? $this->_attributes[$keys[0]] : null;
		} else {
			$values = [];
			foreach ($keys as $name) {
				$values[$name] = isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
			}
			return $values;
		}
	}

	/**
	 * Returns the old primary key value(s).
	 * This refers to the primary key value that is populated into the record
	 * after executing a find method (e.g. find(), findAll()).
	 * The value remains unchanged even if the primary key attribute is manually assigned with a different value.
	 * @param boolean $asArray whether to return the primary key value as an array. If true,
	 * the return value will be an array with column name as key and column value as value.
	 * If this is false (default), a scalar value will be returned for non-composite primary key.
	 * @property mixed The old primary key value. An array (column name => column value) is
	 * returned if the primary key is composite. A string is returned otherwise (null will be
	 * returned if the key value is null).
	 * @return mixed the old primary key value. An array (column name => column value) is returned if the primary key
	 * is composite or `$asArray` is true. A string is returned otherwise (null will be returned if
	 * the key value is null).
	 */
	public function getOldPrimaryKey($asArray = false)
	{
		$keys = $this->primaryKey();
		if (count($keys) === 1 && !$asArray) {
			return isset($this->_oldAttributes[$keys[0]]) ? $this->_oldAttributes[$keys[0]] : null;
		} else {
			$values = [];
			foreach ($keys as $name) {
				$values[$name] = isset($this->_oldAttributes[$name]) ? $this->_oldAttributes[$name] : null;
			}
			return $values;
		}
	}

	/**
	 * Creates an active record object using a row of data.
	 * This method is called by [[ActiveQuery]] to populate the query results
	 * into Active Records. It is not meant to be used to create new records.
	 * @param array $row attribute values (name => value)
	 * @return ActiveRecord the newly created active record.
	 */
	public static function create($row)
	{
		$record = static::instantiate($row);
		$columns = array_flip($record->attributes());
		foreach ($row as $name => $value) {
			if (isset($columns[$name])) {
				$record->_attributes[$name] = $value;
			} else {
				$record->$name = $value;
			}
		}
		$record->_oldAttributes = $record->_attributes;
		$record->afterFind();
		return $record;
	}

	/**
	 * Creates an active record instance.
	 * This method is called by [[create()]].
	 * You may override this method if the instance being created
	 * depends on the row data to be populated into the record.
	 * For example, by creating a record based on the value of a column,
	 * you may implement the so-called single-table inheritance mapping.
	 * @param array $row row data to be populated into the record.
	 * @return ActiveRecord the newly created active record
	 */
	public static function instantiate($row)
	{
		return new static;
	}

	/**
	 * Returns whether there is an element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed $offset the offset to check on
	 * @return boolean whether there is an element at the specified offset.
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
	 * @throws InvalidParamException if the named relation does not exist.
	 */
	public function getRelation($name)
	{
		$getter = 'get' . $name;
		try {
			$relation = $this->$getter();
			if ($relation instanceof ActiveRelationInterface) {
				return $relation;
			} else {
				throw new InvalidParamException(get_class($this) . ' has no relation named "' . $name . '".');
			}
		} catch (UnknownMethodException $e) {
			throw new InvalidParamException(get_class($this) . ' has no relation named "' . $name . '".', 0, $e);
		}
	}
}