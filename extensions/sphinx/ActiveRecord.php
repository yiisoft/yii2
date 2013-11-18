<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\base\ModelEvent;
use yii\base\NotSupportedException;
use yii\base\UnknownMethodException;
use yii\db\ActiveRelationInterface;
use yii\db\Expression;
use yii\db\StaleObjectException;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * Class ActiveRecord
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class ActiveRecord extends Model
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
	 * The insert operation. This is mainly used when overriding [[transactions()]] to specify which operations are transactional.
	 */
	const OP_INSERT = 0x01;
	/**
	 * The update operation. This is mainly used when overriding [[transactions()]] to specify which operations are transactional.
	 */
	const OP_UPDATE = 0x02;
	/**
	 * The delete operation. This is mainly used when overriding [[transactions()]] to specify which operations are transactional.
	 */
	const OP_DELETE = 0x04;
	/**
	 * All three operations: insert, update, delete.
	 * This is a shortcut of the expression: OP_INSERT | OP_UPDATE | OP_DELETE.
	 */
	const OP_ALL = 0x07;

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
	 * @var string snippet value for this Active Record instance.
	 */
	private $_snippet;

	/**
	 * Returns the Sphinx connection used by this AR class.
	 * By default, the "sphinx" application component is used as the Sphinx connection.
	 * You may override this method if you want to use a different Sphinx connection.
	 * @return Connection the Sphinx connection used by this AR class.
	 */
	public static function getDb()
	{
		return \Yii::$app->getComponent('sphinx');
	}

	/**
	 * Creates an [[ActiveQuery]] instance for query purpose.
	 *
	 * @param mixed $q the query parameter. This can be one of the followings:
	 *
	 *  - a string: fulltext query by a query string and return the list
	 *    of matching records.
	 *  - an array of name-value pairs: query by a set of column values and return a single record matching all of them.
	 *  - null: return a new [[ActiveQuery]] object for further query purpose.
	 *
	 * @return ActiveQuery|ActiveRecord[]|ActiveRecord|null When `$q` is null, a new [[ActiveQuery]] instance
	 * is returned; when `$q` is a string, an array of ActiveRecord objects matching it will be returned;
	 * when `$q` is an array, an ActiveRecord object matching it will be returned (null
	 * will be returned if there is no matching).
	 * @see createQuery()
	 */
	public static function find($q = null)
	{
		$query = static::createQuery();
		if (is_array($q)) {
			return $query->where($q)->one();
		} elseif ($q !== null) {
			return $query->match($q)->all();
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
	public static function findBySql($sql, $params = [])
	{
		$query = static::createQuery();
		$query->sql = $sql;
		return $query->params($params);
	}

	/**
	 * Updates the whole table using the provided attribute values and conditions.
	 * For example, to change the status to be 1 for all customers whose status is 2:
	 *
	 * ~~~
	 * Customer::updateAll(['status' => 1], 'status = 2');
	 * ~~~
	 *
	 * @param array $attributes attribute values (name-value pairs) to be saved into the table
	 * @param string|array $condition the conditions that will be put in the WHERE part of the UPDATE SQL.
	 * Please refer to [[Query::where()]] on how to specify this parameter.
	 * @param array $params the parameters (name => value) to be bound to the query.
	 * @return integer the number of rows updated
	 */
	public static function updateAll($attributes, $condition = '', $params = [])
	{
		$command = static::getDb()->createCommand();
		$command->update(static::indexName(), $attributes, $condition, $params);
		return $command->execute();
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
	 * @param array $params the parameters (name => value) to be bound to the query.
	 * @return integer the number of rows deleted
	 */
	public static function deleteAll($condition = '', $params = [])
	{
		$command = static::getDb()->createCommand();
		$command->delete(static::indexName(), $condition, $params);
		return $command->execute();
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
		return new ActiveQuery(['modelClass' => get_called_class()]);
	}

	/**
	 * Declares the name of the database table associated with this AR class.
	 * By default this method returns the class name as the table name by calling [[Inflector::camel2id()]]
	 * with prefix 'tbl_'. For example, 'Customer' becomes 'tbl_customer', and 'OrderItem' becomes
	 * 'tbl_order_item'. You may override this method if the table is not named after this convention.
	 * @return string the table name
	 */
	public static function indexName()
	{
		return Inflector::camel2id(StringHelper::basename(get_called_class()), '_');
	}

	/**
	 * Returns the schema information of the DB table associated with this AR class.
	 * @return IndexSchema the schema information of the DB table associated with this AR class.
	 * @throws InvalidConfigException if the table for the AR class does not exist.
	 */
	public static function getIndexSchema()
	{
		$schema = static::getDb()->getIndexSchema(static::indexName());
		if ($schema !== null) {
			return $schema;
		} else {
			throw new InvalidConfigException("The index does not exist: " . static::indexName());
		}
	}

	/**
	 * Returns the primary key name for this AR class.
	 * @return string the primary keys of the associated database table.
	 */
	public static function primaryKey()
	{
		return static::getIndexSchema()->primaryKey;
	}

	/**
	 * Builds a snippet from provided data and query, using specified index settings.
	 * @param string|array $source is the source data to extract a snippet from.
	 * It could be either a single string or array of strings.
	 * @param string $query the full-text query to build snippets for.
	 * @param array $options list of options in format: optionName => optionValue
	 * @return string|array built snippet in case "source" is a string, list of built snippets
	 * in case "source" is an array.
	 */
	public static function callSnippets($source, $query, $options = [])
	{
		$command = static::getDb()->createCommand();
		$command->callSnippets(static::indexName(), $source, $query, $options);
		if (is_array($source)) {
			return $command->queryColumn();
		} else {
			return $command->queryScalar();
		}
	}

	/**
	 * Returns tokenized and normalized forms of the keywords, and, optionally, keyword statistics.
	 * @param string $text the text to break down to keywords.
	 * @param boolean $fetchStatistic whether to return document and hit occurrence statistics
	 * @return array keywords and statistics
	 */
	public static function callKeywords($text, $fetchStatistic = false)
	{
		$command = static::getDb()->createCommand();
		$command->callKeywords(static::indexName(), $text, $fetchStatistic);
		return $command->queryAll();
	}

	/**
	 * @param string $snippet
	 */
	public function setSnippet($snippet)
	{
		$this->_snippet = $snippet;
	}

	/**
	 * @param string $query snippet source query
	 * @param array $options list of options in format: optionName => optionValue
	 * @return string snippet value
	 */
	public function getSnippet($query = null, $options = [])
	{
		if ($query !== null) {
			$this->_snippet = $this->fetchSnippet($query, $options);
		}
		return $this->_snippet;
	}

	/**
	 * Builds up the snippet value from the given query.
	 * @param string $query the full-text query to build snippets for.
	 * @param array $options list of options in format: optionName => optionValue
	 * @return string snippet value.
	 */
	protected function fetchSnippet($query, $options = [])
	{
		return static::callSnippets($this->getSnippetSource(), $query, $options);
	}

	/**
	 * Returns the string, which should be used as a source to create snippet for this
	 * Active Record instance.
	 * Child classes must implement this method to return the actual snippet source text.
	 * For example:
	 * ```php
	 * public function getSnippetSource()
	 * {
	 *     return $this->snippetSourceRelation->content;
	 * }
	 * ```
	 * @return string snippet source string.
	 * @throws \yii\base\NotSupportedException if this is not supported by the Active Record class
	 */
	public function getSnippetSource()
	{
		throw new NotSupportedException($this->className() . ' does not provide snippet source.');
	}

	/**
	 * Returns the name of the column that stores the lock version for implementing optimistic locking.
	 *
	 * Optimistic locking allows multiple users to access the same record for edits and avoids
	 * potential conflicts. In case when a user attempts to save the record upon some staled data
	 * (because another user has modified the data), a [[StaleObjectException]] exception will be thrown,
	 * and the update or deletion is skipped.
	 *
	 * Optimized locking is only supported by [[update()]] and [[delete()]].
	 *
	 * To use optimized locking:
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
	 * Declares which DB operations should be performed within a transaction in different scenarios.
	 * The supported DB operations are: [[OP_INSERT]], [[OP_UPDATE]] and [[OP_DELETE]],
	 * which correspond to the [[insert()]], [[update()]] and [[delete()]] methods, respectively.
	 * By default, these methods are NOT enclosed in a DB transaction.
	 *
	 * In some scenarios, to ensure data consistency, you may want to enclose some or all of them
	 * in transactions. You can do so by overriding this method and returning the operations
	 * that need to be transactional. For example,
	 *
	 * ~~~
	 * return [
	 *     'admin' => self::OP_INSERT,
	 *     'api' => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
	 *     // the above is equivalent to the following:
	 *     // 'api' => self::OP_ALL,
	 *
	 * ];
	 * ~~~
	 *
	 * The above declaration specifies that in the "admin" scenario, the insert operation ([[insert()]])
	 * should be done in a transaction; and in the "api" scenario, all the operations should be done
	 * in a transaction.
	 *
	 * @return array the declarations of transactional operations. The array keys are scenarios names,
	 * and the array values are the corresponding transaction operations.
	 */
	public function transactions()
	{
		return [];
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
		return array_keys($this->getIndexSchema()->columns);
	}

	/**
	 * Returns a value indicating whether the model has an attribute with the specified name.
	 * @param string $name the name of the attribute
	 * @return boolean whether the model has an attribute with the specified name.
	 */
	public function hasAttribute($name)
	{
		return isset($this->_attributes[$name]) || isset($this->getIndexSchema()->columns[$name]);
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
	 * @throws InvalidParamException if the named attribute does not exist.
	 * @see hasAttribute
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
	 * @throws InvalidParamException if the named attribute does not exist.
	 * @see hasAttribute
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
	 * @throws \Exception in case insert failed.
	 */
	public function insert($runValidation = true, $attributes = null)
	{
		if ($runValidation && !$this->validate($attributes)) {
			return false;
		}
		$db = static::getDb();
		if ($this->isTransactional(self::OP_INSERT) && $db->getTransaction() === null) {
			$transaction = $db->beginTransaction();
			try {
				$result = $this->insertInternal($attributes);
				if ($result === false) {
					$transaction->rollback();
				} else {
					$transaction->commit();
				}
			} catch (\Exception $e) {
				$transaction->rollback();
				throw $e;
			}
		} else {
			$result = $this->insertInternal($attributes);
		}
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
			$key = $this->primaryKey();
			$values[$key] = isset($this->_attributes[$key]) ? $this->_attributes[$key] : null;
		}
		$db = static::getDb();
		$command = $db->createCommand()->insert($this->indexName(), $values);
		if (!$command->execute()) {
			return false;
		}
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
		$db = static::getDb();
		if ($this->isTransactional(self::OP_UPDATE) && $db->getTransaction() === null) {
			$transaction = $db->beginTransaction();
			try {
				$result = $this->updateInternal($attributes);
				if ($result === false) {
					$transaction->rollback();
				} else {
					$transaction->commit();
				}
			} catch (\Exception $e) {
				$transaction->rollback();
				throw $e;
			}
		} else {
			$result = $this->updateInternal($attributes);
		}
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

		// Replace is supported only by runtime indexes and necessary only for field update
		$useReplace = false;
		$indexSchema = $this->getIndexSchema();
		if ($this->getIndexSchema()->isRuntime) {
			foreach ($values as $name => $value) {
				$columnSchema = $indexSchema->getColumn($name);
				if ($columnSchema->isField) {
					$useReplace = true;
					break;
				}
			}
		}

		if ($useReplace) {
			$values = array_merge($values, $this->getOldPrimaryKey(true));
			$command = static::getDb()->createCommand();
			$command->replace(static::indexName(), $values);
			// We do not check the return value of replace because it's possible
			// that the REPLACE statement doesn't change anything and thus returns 0.
			$rows = $command->execute();
		} else {
			$condition = $this->getOldPrimaryKey(true);
			$lock = $this->optimisticLock();
			if ($lock !== null) {
				if (!isset($values[$lock])) {
					$values[$lock] = $this->$lock + 1;
				}
				$condition[$lock] = $this->$lock;
			}
			// We do not check the return value of updateAll() because it's possible
			// that the UPDATE statement doesn't change anything and thus returns 0.
			$rows = $this->updateAll($values, $condition);

			if ($lock !== null && !$rows) {
				throw new StaleObjectException('The object being updated is outdated.');
			}
		}

		foreach ($values as $name => $value) {
			$this->_oldAttributes[$name] = $this->_attributes[$name];
		}
		$this->afterSave(false);
		return $rows;
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
		$db = static::getDb();
		$transaction = $this->isTransactional(self::OP_DELETE) && $db->getTransaction() === null ? $db->beginTransaction() : null;
		try {
			$result = false;
			if ($this->beforeDelete()) {
				// we do not check the return value of deleteAll() because it's possible
				// the record is already deleted in the database and thus the method will return 0
				$condition = $this->getOldPrimaryKey(true);
				$lock = $this->optimisticLock();
				if ($lock !== null) {
					$condition[$lock] = $this->$lock;
				}
				$result = $this->deleteAll($condition);
				if ($lock !== null && !$result) {
					throw new StaleObjectException('The object being deleted is outdated.');
				}
				$this->_oldAttributes = null;
				$this->afterDelete();
			}
			if ($transaction !== null) {
				if ($result === false) {
					$transaction->rollback();
				} else {
					$transaction->commit();
				}
			}
		} catch (\Exception $e) {
			if ($transaction !== null) {
				$transaction->rollback();
			}
			throw $e;
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
	 * @see getIsNewRecord
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
			$this->_attributes[$name] = $record->_attributes[$name];
		}
		$this->_oldAttributes = $this->_attributes;
		$this->_related = [];
		return true;
	}

	/**
	 * Returns a value indicating whether the given active record is the same as the current one.
	 * The comparison is made by comparing the index names and the primary key values of the two active records.
	 * @param ActiveRecord $record record to compare to
	 * @return boolean whether the two active records refer to the same row in the same index.
	 */
	public function equals($record)
	{
		return $this->indexName() === $record->indexName() && $this->getPrimaryKey() === $record->getPrimaryKey();
	}

	/**
	 * Returns the primary key value.
	 * @param boolean $asArray whether to return the primary key value as an array. If true,
	 * the return value will be an array with column names as keys and column values as values.
	 * @return mixed the primary key value. An array (column name => column value) is returned
	 * if `$asArray` is true. A string is returned otherwise (null will be returned if
	 * the key value is null).
	 */
	public function getPrimaryKey($asArray = false)
	{
		$key = $this->primaryKey();
		$value = isset($this->_attributes[$key]) ? $this->_attributes[$key] : null;
		if ($asArray) {
			return [$key => $value];
		} else {
			return $value;
		}
	}

	/**
	 * Returns the old primary key value.
	 * This refers to the primary key value that is populated into the record
	 * after executing a find method (e.g. find(), findAll()).
	 * The value remains unchanged even if the primary key attribute is manually assigned with a different value.
	 * @param boolean $asArray whether to return the primary key value as an array. If true,
	 * the return value will be an array with column name as key and column value as value.
	 * If this is false (default), a scalar value will be returned.
	 * @return mixed the old primary key value. An array (column name => column value) is returned if
	 * `$asArray` is true. A string is returned otherwise (null will be returned if
	 * the key value is null).
	 */
	public function getOldPrimaryKey($asArray = false)
	{
		$key = $this->primaryKey();
		$value = isset($this->_oldAttributes[$key]) ? $this->_oldAttributes[$key] : null;
		if ($asArray) {
			return [$key => $value];
		} else {
			return $value;
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
		$columns = static::getIndexSchema()->columns;
		foreach ($row as $name => $value) {
			if (isset($columns[$name])) {
				$column = $columns[$name];
				if ($column->isMva) {
					$value = explode(',', $value);
					$value = array_map([$column, 'typecast'], $value);
				} else {
					$value = $column->typecast($value);
				}
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
	 * A relation is defined by a getter method which returns an [[ActiveRelationInterface]] object.
	 * It can be declared in either the Active Record class itself or one of its behaviors.
	 * @param string $name the relation name
	 * @return ActiveRelationInterface the relation object
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
				return null;
			}
		} catch (UnknownMethodException $e) {
			throw new InvalidParamException(get_class($this) . ' has no relation named "' . $name . '".', 0, $e);
		}
	}

	/**
	 * Returns a value indicating whether the specified operation is transactional in the current [[scenario]].
	 * @param integer $operation the operation to check. Possible values are [[OP_INSERT]], [[OP_UPDATE]] and [[OP_DELETE]].
	 * @return boolean whether the specified operation is transactional in the current [[scenario]].
	 */
	public function isTransactional($operation)
	{
		$scenario = $this->getScenario();
		$transactions = $this->transactions();
		return isset($transactions[$scenario]) && ($transactions[$scenario] & $operation);
	}

	/**
	 * Sets the element at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `$model[$offset] = $item;`.
	 * @param integer $offset the offset to set element
	 * @param mixed $item the element value
	 * @throws \Exception on failure
	 */
	public function offsetSet($offset, $item)
	{
		// Bypass relation owner restriction to 'yii\db\ActiveRecord' at [[yii\db\ActiveRelationTrait::findWith()]]:
		try {
			$relation = $this->getRelation($offset);
			if (is_object($relation)) {
				$this->populateRelation($offset, $item);
				return;
			}
		} catch (UnknownMethodException $e) {
			throw $e->getPrevious();
		}
		parent::offsetSet($offset, $item);
	}
}