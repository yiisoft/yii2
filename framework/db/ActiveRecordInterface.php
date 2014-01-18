<?php
/**
 * 
 * 
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yii\db;

/**
 * ActiveRecordInterface
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
interface ActiveRecordInterface
{
	/**
	 * Returns the primary key **name(s)** for this AR class.
	 *
	 * Note that an array should be returned even when the record only has a single primary key.
	 *
	 * For the primary key **value** see [[getPrimaryKey()]] instead.
	 *
	 * @return string[] the primary key name(s) for this AR class.
	 */
	public static function primaryKey();

	/**
	 * Returns the list of all attribute names of the record.
	 * @return array list of attribute names.
	 */
	public function attributes();

	/**
	 * Returns the named attribute value.
	 * If this record is the result of a query and the attribute is not loaded,
	 * null will be returned.
	 * @param string $name the attribute name
	 * @return mixed the attribute value. Null if the attribute is not set or does not exist.
	 * @see hasAttribute()
	 */
	public function getAttribute($name);

	/**
	 * Sets the named attribute value.
	 * @param string $name the attribute name.
	 * @param mixed $value the attribute value.
	 * @see hasAttribute()
	 */
	public function setAttribute($name, $value);

	/**
	 * Returns a value indicating whether the record has an attribute with the specified name.
	 * @param string $name the name of the attribute
	 * @return boolean whether the record has an attribute with the specified name.
	 */
	public function hasAttribute($name);

	/**
	 * Returns the primary key value(s).
	 * @param boolean $asArray whether to return the primary key value as an array. If true,
	 * the return value will be an array with attribute names as keys and attribute values as values.
	 * Note that for composite primary keys, an array will always be returned regardless of this parameter value.
	 * @return mixed the primary key value. An array (attribute name => attribute value) is returned if the primary key
	 * is composite or `$asArray` is true. A string is returned otherwise (null will be returned if
	 * the key value is null).
	 */
	public function getPrimaryKey($asArray = false);

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
	public function getOldPrimaryKey($asArray = false);

	/**
	 * Creates an [[ActiveQueryInterface|ActiveQuery]] instance for query purpose.
	 *
	 * This method is usually ment to be used like this:
	 *
	 * ```php
	 * Customer::find(1); // find one customer by primary key
	 * Customer::find()->all(); // find all customers
	 * ```
	 *
	 * @param mixed $q the query parameter. This can be one of the followings:
	 *
	 *  - a scalar value (integer or string): query by a single primary key value and return the
	 *    corresponding record.
	 *  - an array of name-value pairs: query by a set of attribute values and return a single record matching all of them.
	 *  - null (not specified): return a new [[ActiveQuery]] object for further query purpose.
	 *
	 * @return ActiveQueryInterface|static|null When `$q` is null, a new [[ActiveQuery]] instance
	 * is returned; when `$q` is a scalar or an array, an ActiveRecord object matching it will be
	 * returned (null will be returned if there is no matching).
	 */
	public static function find($q = null);

	/**
	 * Creates an [[ActiveQueryInterface|ActiveQuery]] instance.
	 *
	 * This method is called by [[find()]] to start a SELECT query.
	 * You may override this method to return a customized query (e.g. `CustomerQuery` specified
	 * written for querying `Customer` purpose.)
	 *
	 * You may also define default conditions that should apply to all queries unless overridden:
	 *
	 * ```php
	 * public static function createQuery()
	 * {
	 *     return parent::createQuery()->where(['deleted' => false]);
	 * }
	 * ```
	 *
	 * Note that all queries should use [[Query::andWhere()]] and [[Query::orWhere()]] to keep the
	 * default condition. Using [[Query::where()]] will override the default condition.
	 *
	 * @return ActiveQueryInterface the newly created [[ActiveQueryInterface|ActiveQuery]] instance.
	 */
	public static function createQuery();

	/**
	 * Updates records using the provided attribute values and conditions.
	 * For example, to change the status to be 1 for all customers whose status is 2:
	 *
	 * ~~~
	 * Customer::updateAll(['status' => 1], ['status' => '2']);
	 * ~~~
	 *
	 * @param array $attributes attribute values (name-value pairs) to be saved for the record.
	 * Unlike [[update()]] these are not going to be validated.
	 * @param array $condition the condition that matches the records that should get updated.
	 * Please refer to [[QueryInterface::where()]] on how to specify this parameter.
	 * An empty condition will match all records.
	 * @return integer the number of rows updated
	 */
	public static function updateAll($attributes, $condition = null);

	/**
	 * Deletes records using the provided conditions.
	 * WARNING: If you do not specify any condition, this method will delete ALL rows in the table.
	 *
	 * For example, to delete all customers whose status is 3:
	 *
	 * ~~~
	 * Customer::deleteAll([status = 3]);
	 * ~~~
	 *
	 * @param array $condition the condition that matches the records that should get deleted.
	 * Please refer to [[QueryInterface::where()]] on how to specify this parameter.
	 * An empty condition will match all records.
	 * @return integer the number of rows deleted
	 */
	public static function deleteAll($condition = null);

	/**
	 * Saves the current record.
	 *
	 * This method will call [[insert()]] when [[isNewRecord]] is true, or [[update()]]
	 * when [[isNewRecord]] is false.
	 *
	 * For example, to save a customer record:
	 *
	 * ~~~
	 * $customer = new Customer; // or $customer = Customer::find($id);
	 * $customer->name = $name;
	 * $customer->email = $email;
	 * $customer->save();
	 * ~~~
	 *
	 * @param boolean $runValidation whether to perform validation before saving the record.
	 * If the validation fails, the record will not be saved to database. `false` will be returned
	 * in this case.
	 * @param array $attributes list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved.
	 * @return boolean whether the saving succeeds
	 */
	public function save($runValidation = true, $attributes = null);

	/**
	 * Inserts the record into the database using the attribute values of this record.
	 *
	 * Usage example:
	 *
	 * ```php
	 * $customer = new Customer;
	 * $customer->name = $name;
	 * $customer->email = $email;
	 * $customer->insert();
	 * ```
	 *
	 * @param boolean $runValidation whether to perform validation before saving the record.
	 * If the validation fails, the record will not be inserted into the database.
	 * @param array $attributes list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved.
	 * @return boolean whether the attributes are valid and the record is inserted successfully.
	 */
	public function insert($runValidation = true, $attributes = null);

	/**
	 * Saves the changes to this active record into the database.
	 *
	 * Usage example:
	 *
	 * ```php
	 * $customer = Customer::find($id);
	 * $customer->name = $name;
	 * $customer->email = $email;
	 * $customer->update();
	 * ```
	 *
	 * @param boolean $runValidation whether to perform validation before saving the record.
	 * If the validation fails, the record will not be inserted into the database.
	 * @param array $attributes list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved.
	 * @return integer|boolean the number of rows affected, or false if validation fails
	 * or updating process is stopped for other reasons.
	 * Note that it is possible that the number of rows affected is 0, even though the
	 * update execution is successful.
	 */
	public function update($runValidation = true, $attributes = null);

	/**
	 * Deletes the record from the database.
	 *
	 * @return integer|boolean the number of rows deleted, or false if the deletion is unsuccessful for some reason.
	 * Note that it is possible that the number of rows deleted is 0, even though the deletion execution is successful.
	 */
	public function delete();

	/**
	 * Returns a value indicating whether the current record is new (not saved in the database).
	 * @return boolean whether the record is new and should be inserted when calling [[save()]].
	 */
	public function getIsNewRecord();

	/**
	 * Returns a value indicating whether the given active record is the same as the current one.
	 * Two [[isNewRecord|new]] records are considered to be not equal.
	 * @param static $record record to compare to
	 * @return boolean whether the two active records refer to the same row in the same database table.
	 */
	public function equals($record);

	/**
	 * Creates an [[ActiveRelationInterface|ActiveRelation]] instance.
	 * This method is called by [[BaseActiveRecord::hasOne()]] and [[BaseActiveRecord::hasMany()]] to
	 * create a relation instance.
	 * You may override this method to return a customized relation.
	 * @param array $config the configuration passed to the ActiveRelation class.
	 * @return ActiveRelation the newly created [[ActiveRelation]] instance.
	 */
	public static function createActiveRelation($config = []);

	/**
	 * Returns the relation object with the specified name.
	 * A relation is defined by a getter method which returns an [[ActiveRelationInterface|ActiveRelation]] object.
	 * It can be declared in either the ActiveRecord class itself or one of its behaviors.
	 * @param string $name the relation name
	 * @return ActiveRelation the relation object
	 */
	public function getRelation($name);

	/**
	 * Establishes the relationship between two records.
	 *
	 * The relationship is established by setting the foreign key value(s) in one record
	 * to be the corresponding primary key value(s) in the other record.
	 * The record with the foreign key will be saved into database without performing validation.
	 *
	 * If the relationship involves a pivot table, a new row will be inserted into the
	 * pivot table which contains the primary key values from both records.
	 *
	 * This method requires that the primary key value is not null.
	 *
	 * @param string $name the case sensitive name of the relationship.
	 * @param static $model the record to be linked with the current one.
	 * @param array $extraColumns additional column values to be saved into the pivot table.
	 * This parameter is only meaningful for a relationship involving a pivot table
	 * (i.e., a relation set with `[[ActiveRelationInterface::via()]]`.)
	 */
	public function link($name, $model, $extraColumns = []);

	/**
	 * Destroys the relationship between two records.
	 *
	 * The record with the foreign key of the relationship will be deleted if `$delete` is true.
	 * Otherwise, the foreign key will be set null and the record will be saved without validation.
	 *
	 * @param string $name the case sensitive name of the relationship.
	 * @param static $model the model to be unlinked from the current one.
	 * @param boolean $delete whether to delete the model that contains the foreign key.
	 * If false, the model's foreign key will be set null and saved.
	 * If true, the model containing the foreign key will be deleted.
	 */
	public function unlink($name, $model, $delete = false);
}
