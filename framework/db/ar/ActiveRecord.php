<?php
/**
 * ActiveRecord class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\ar;

use yii\db\Exception;
use yii\db\dao\Connection;
use yii\db\dao\TableSchema;
use yii\db\dao\Query;

/**
 * ActiveRecord is the base class for classes representing relational data.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @property array $attributes
 */
abstract class ActiveRecord extends \yii\base\Model
{
	/**
	 * @var
	 */
	private static $_md;

	private $_new = false; // whether this instance is new or not
	private $_attributes = array(); // attribute name => attribute value
	private $_oldAttributes;
	private $_related = array(); // attribute name => related objects
	private $_pk; // old primary key value

	/**
	 * Returns the metadata for this AR class.
	 * @param boolean $refresh whether to rebuild the metadata.
	 * @return ActiveMetaData the meta for this AR class.
	 */
	public static function getMetaData($refresh = false)
	{
		$class = get_called_class();
		if (!$refresh && isset(self::$_md[$class])) {
			return self::$_md[$class];
		} else {
			return self::$_md[$class] = new ActiveMetaData('\\' . $class);
		}
	}

	/**
	 * @static
	 * @param string|array|ActiveQuery $q
	 * @return ActiveQuery
	 * @throws \yii\db\Exception
	 */
	public static function find($q = null)
	{
		$query = $q instanceof ActiveQuery? $q : static::createQuery();
		$query->modelClass = '\\' . get_called_class();
		$query->from = static::tableName();
		if (is_array($q)) {
			$query->where($q);
		} elseif ($q !== null && $query !== $q) {
			$primaryKey = static::getMetaData()->table->primaryKey;
			if (is_string($primaryKey)) {
				$query->where(array($primaryKey => $q));
			} else {
				throw new Exception("Multiple column values are required to find by composite primary keys.");
			}
		}
		return $query;
	}

	public static function findBySql($sql, $params = array())
	{
		$query = static::createQuery();
		if (!is_array($params)) {
			$params = func_get_args();
			array_shift($params);
		}
		$query->setSql($sql);
		$query->modelClass = '\\' . get_called_class();
		return $query->params($params);
	}

	public static function exists($condition, $params)
	{

	}

	public static function updateAll()
	{

	}

	public static function updateCounters()
	{

	}

	public static function deleteAll()
	{

	}

	public static function createQuery()
	{
		return new ActiveQuery('\\' . get_called_class());
	}

	/**
	 * Returns the database connection used by active record.
	 * By default, the "db" application component is used as the database connection.
	 * You may override this method if you want to use a different database connection.
	 * @return Connection the database connection used by active record.
	 */
	public static function getDbConnection()
	{
		return \Yii::$application->getDb();
	}

	/**
	 * Returns the default named scope that should be implicitly applied to all queries for this model.
	 * Note, default scope only applies to SELECT queries. It is ignored for INSERT, UPDATE and DELETE queries.
	 * The default implementation simply returns an empty array. You may override this method
	 * if the model needs to be queried with some default criteria (e.g. only active records should be returned).
	 * @return array the query criteria. This will be used as the parameter to the constructor
	 * of {@link CDbCriteria}.
	 */
	public static function defaultScope()
	{
		return array();
	}

	/**
	 * Returns the name of the associated database table.
	 * By default this method returns the class name as the table name.
	 * You may override this method if the table is not named after this convention.
	 * @return string the table name
	 */
	public static function tableName()
	{
		return basename(get_called_class());
	}

	/**
	 * Returns the primary key of the associated database table.
	 * This method is meant to be overridden in case when the table is not defined with a primary key
	 * (for some legacy database). If the table is already defined with a primary key,
	 * you do not need to override this method. The default implementation simply returns null,
	 * meaning using the primary key defined in the database.
	 * @return mixed the primary key of the associated database table.
	 * If the key is a single column, it should return the column name;
	 * If the key is a composite one consisting of several columns, it should
	 * return the array of the key column names.
	 */
	public static function primaryKey()
	{
	}

	/**
	 * Declares the relations for this ActiveRecord class.
	 *
	 * Child classes may want to override this method to specify their relations.
	 *
	 * The following shows how to declare relations for a Programmer AR class:
	 *
	 * ~~~
	 * return array(
	 *     'manager:Manager' => '?.manager_id = manager.id',
	 *     'assignments:Assignment[]' => array(
	 *         'on' => '?.id = assignments.owner_id AND assignments.status=1',
	 *         'orderBy' => 'assignments.create_time DESC',
	 *     ),
	 *     'projects:Project[]' => array(
	 *         'via' => 'assignments',
	 *         'on' => 'projects.id = assignments.project_id',
	 *     ),
	 * );
	 * ~~~
	 *
	 * This method should be overridden to declare related objects.
	 *
	 * There are four types of relations that may exist between two active record objects:
	 * <ul>
	 * <li>BELONGS_TO: e.g. a member belongs to a team;</li>
	 * <li>HAS_ONE: e.g. a member has at most one profile;</li>
	 * <li>HAS_MANY: e.g. a team has many members;</li>
	 * <li>MANY_MANY: e.g. a member has many skills and a skill belongs to a member.</li>
	 * </ul>
	 *
	 * Besides the above relation types, a special relation called STAT is also supported
	 * that can be used to perform statistical query (or aggregational query).
	 * It retrieves the aggregational information about the related objects, such as the number
	 * of comments for each post, the average rating for each product, etc.
	 *
	 * Each kind of related objects is defined in this method as an array with the following elements:
	 * <pre>
	 * 'varName'=>array('relationType', 'className', 'foreign_key', ...additional options)
	 * </pre>
	 * where 'varName' refers to the name of the variable/property that the related object(s) can
	 * be accessed through; 'relationType' refers to the type of the relation, which can be one of the
	 * following four constants: self::BELONGS_TO, self::HAS_ONE, self::HAS_MANY and self::MANY_MANY;
	 * 'className' refers to the name of the active record class that the related object(s) is of;
	 * and 'foreign_key' states the foreign key that relates the two kinds of active record.
	 * Note, for composite foreign keys, they must be listed together, separated by commas;
	 * and for foreign keys used in MANY_MANY relation, the joining table must be declared as well
	 * (e.g. 'join_table(fk1, fk2)').
	 *
	 * Additional options may be specified as name-value pairs in the rest array elements:
	 * <ul>
	 * <li>'select': string|array, a list of columns to be selected. Defaults to '*', meaning all columns.
	 *   Column names should be disambiguated if they appear in an expression (e.g. COUNT(relationName.name) AS name_count).</li>
	 * <li>'condition': string, the WHERE clause. Defaults to empty. Note, column references need to
	 *   be disambiguated with prefix 'relationName.' (e.g. relationName.age&gt;20)</li>
	 * <li>'order': string, the ORDER BY clause. Defaults to empty. Note, column references need to
	 *   be disambiguated with prefix 'relationName.' (e.g. relationName.age DESC)</li>
	 * <li>'with': string|array, a list of child related objects that should be loaded together with this object.
	 *   Note, this is only honored by lazy loading, not eager loading.</li>
	 * <li>'joinType': type of join. Defaults to 'LEFT OUTER JOIN'.</li>
	 * <li>'alias': the alias for the table associated with this relationship.
	 *   This option has been available since version 1.0.1. It defaults to null,
	 *   meaning the table alias is the same as the relation name.</li>
	 * <li>'params': the parameters to be bound to the generated SQL statement.
	 *   This should be given as an array of name-value pairs. This option has been
	 *   available since version 1.0.3.</li>
	 * <li>'on': the ON clause. The condition specified here will be appended
	 *   to the joining condition using the AND operator. This option has been
	 *   available since version 1.0.2.</li>
	 * <li>'index': the name of the column whose values should be used as keys
	 *   of the array that stores related objects. This option is only available to
	 *   HAS_MANY and MANY_MANY relations. This option has been available since version 1.0.7.</li>
	 * <li>'scopes': scopes to apply. In case of a single scope can be used like 'scopes'=>'scopeName',
	 *   in case of multiple scopes can be used like 'scopes'=>array('scopeName1','scopeName2').
	 *   This option has been available since version 1.1.9.</li>
	 * </ul>
	 *
	 * The following options are available for certain relations when lazy loading:
	 * <ul>
	 * <li>'group': string, the GROUP BY clause. Defaults to empty. Note, column references need to
	 *   be disambiguated with prefix 'relationName.' (e.g. relationName.age). This option only applies to HAS_MANY and MANY_MANY relations.</li>
	 * <li>'having': string, the HAVING clause. Defaults to empty. Note, column references need to
	 *   be disambiguated with prefix 'relationName.' (e.g. relationName.age). This option only applies to HAS_MANY and MANY_MANY relations.</li>
	 * <li>'limit': limit of the rows to be selected. This option does not apply to BELONGS_TO relation.</li>
	 * <li>'offset': offset of the rows to be selected. This option does not apply to BELONGS_TO relation.</li>
	 * <li>'through': name of the model's relation that will be used as a bridge when getting related data. Can be set only for HAS_ONE and HAS_MANY. This option has been available since version 1.1.7.</li>
	 * </ul>
	 *
	 * Below is an example declaring related objects for 'Post' active record class:
	 * <pre>
	 * return array(
	 *	 'author'=>array(self::BELONGS_TO, 'User', 'author_id'),
	 *	 'comments'=>array(self::HAS_MANY, 'Comment', 'post_id', 'with'=>'author', 'order'=>'create_time DESC'),
	 *	 'tags'=>array(self::MANY_MANY, 'Tag', 'post_tag(post_id, tag_id)', 'order'=>'name'),
	 * );
	 * </pre>
	 *
	 * @return array list of related object declarations. Defaults to empty array.
	 */
	public static function relations()
	{
		return array();
	}

	/**
	 * Returns the declaration of named scopes.
	 * A named scope represents a query criteria that can be chained together with
	 * other named scopes and applied to a query. This method should be overridden
	 * by child classes to declare named scopes for the particular AR classes.
	 * For example, the following code declares two named scopes: 'recently' and
	 * 'published'.
	 * <pre>
	 * return array(
	 *	 'published'=>array(
	 *		   'condition'=>'status=1',
	 *	 ),
	 *	 'recently'=>array(
	 *		   'order'=>'create_time DESC',
	 *		   'limit'=>5,
	 *	 ),
	 * );
	 * </pre>
	 * If the above scopes are declared in a 'Post' model, we can perform the following
	 * queries:
	 * <pre>
	 * $posts=Post::model()->published()->findAll();
	 * $posts=Post::model()->published()->recently()->findAll();
	 * $posts=Post::model()->published()->with('comments')->findAll();
	 * </pre>
	 * Note that the last query is a relational query.
	 *
	 * @return array the scope definition. The array keys are scope names; the array
	 * values are the corresponding scope definitions. Each scope definition is represented
	 * as an array whose keys must be properties of {@link CDbCriteria}.
	 */
	public static function scopes()
	{
		return array();
	}

	/**
	 * Constructor.
	 * @param string $scenario scenario name. See {@link CModel::scenario} for more details about this parameter.
	 */
	public function __construct($scenario = 'insert')
	{
		if ($scenario === null) // internally used by populateRecord() and model()
		{
			return;
		}

		$this->setScenario($scenario);
		$this->setIsNewRecord(true);
	}

	/**
	 * PHP sleep magic method.
	 * This method ensures that the model meta data reference is set to null.
	 * @return array
	 */
	public function __sleep()
	{
		return array_keys((array)$this);
	}

	/**
	 * PHP getter magic method.
	 * This method is overridden so that AR attributes can be accessed like properties.
	 * @param string $name property name
	 * @return mixed property value
	 * @see getAttribute
	 */
	public function __get($name)
	{
		if (isset($this->_attributes[$name])) {
			return $this->_attributes[$name];
		} elseif (isset($this->getMetaData()->table->columns[$name])) {
			return null;
		} elseif (isset($this->_related[$name])) {
			return $this->_related[$name];
		} elseif (isset($this->getMetaData()->relations[$name])) {
			return $this->getRelatedRecord($name);
		} else {
			return parent::__get($name);
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
		if (isset($this->getMetaData()->table->columns[$name])) {
			$this->_attributes[$name] = $value;
		} elseif (isset($this->getMetaData()->relations[$name])) {
			$this->_related[$name] = $value;
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
		if (isset($this->_attributes[$name])) {
			return true;
		}
		elseif (isset($this->getMetaData()->columns[$name]))
		{
			return false;
		}
		elseif (isset($this->_related[$name]))
		{
			return true;
		}
		elseif (isset($this->getMetaData()->relations[$name]))
		{
			return $this->getRelatedRecord($name) !== null;
		}
		else
		{
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
		if (isset($this->getMetaData()->columns[$name])) {
			unset($this->_attributes[$name]);
		}
		elseif (isset($this->getMetaData()->relations[$name]))
		{
			unset($this->_related[$name]);
		}
		else
		{
			parent::__unset($name);
		}
	}

	/**
	 * Calls the named method which is not a class method.
	 * Do not call this method. This is a PHP magic method that we override
	 * to implement the named scope feature.
	 * @param string $name the method name
	 * @param array $parameters method parameters
	 * @return mixed the method return value
	 */
	public function __call($name, $parameters)
	{
		if (isset($this->getMetaData()->relations[$name])) {
			if (empty($parameters)) {
				return $this->getRelatedRecord($name, false);
			}
			else
			{
				return $this->getRelatedRecord($name, false, $parameters[0]);
			}
		}

		$scopes = $this->scopes();
		if (isset($scopes[$name])) {
			$this->getDbCriteria()->mergeWith($scopes[$name]);
			return $this;
		}

		return parent::__call($name, $parameters);
	}

	/**
	 * Returns the related record(s).
	 * This method will return the related record(s) of the current record.
	 * If the relation is HAS_ONE or BELONGS_TO, it will return a single object
	 * or null if the object does not exist.
	 * If the relation is HAS_MANY or MANY_MANY, it will return an array of objects
	 * or an empty array.
	 * @param string $name the relation name (see {@link relations})
	 * @param boolean $refresh whether to reload the related objects from database. Defaults to false.
	 * @param array $params additional parameters that customize the query conditions as specified in the relation declaration.
	 * This parameter has been available since version 1.0.5.
	 * @return mixed the related object(s).
	 * @throws Exception if the relation is not specified in {@link relations}.
	 */
	public function getRelatedRecord($name, $refresh = false, $params = array())
	{
		if (!$refresh && $params === array() && (isset($this->_related[$name]) || array_key_exists($name, $this->_related))) {
			return $this->_related[$name];
		}

		$md = $this->getMetaData();
		if (!isset($md->relations[$name])) {
			throw new Exception(Yii::t('yii', '{class} does not have relation "{name}".',
				array('{class}' => get_class($this), '{name}' => $name)));
		}

		Yii::trace('lazy loading ' . get_class($this) . '.' . $name, 'system.db.ar.ActiveRecord');
		$relation = $md->relations[$name];
		if ($this->getIsNewRecord() && !$refresh && ($relation instanceof CHasOneRelation || $relation instanceof CHasManyRelation)) {
			return $relation instanceof CHasOneRelation ? null : array();
		}

		if ($params !== array()) // dynamic query
		{
			$exists = isset($this->_related[$name]) || array_key_exists($name, $this->_related);
			if ($exists) {
				$save = $this->_related[$name];
			}
			$r = array($name => $params);
		} else
		{
			$r = $name;
		}
		unset($this->_related[$name]);

		$finder = new CActiveFinder($this, $r);
		$finder->lazyFind($this);

		if (!isset($this->_related[$name])) {
			if ($relation instanceof CHasManyRelation) {
				$this->_related[$name] = array();
			}
			elseif ($relation instanceof CStatRelation)
			{
				$this->_related[$name] = $relation->defaultValue;
			}
			else
			{
				$this->_related[$name] = null;
			}
		}

		if ($params !== array()) {
			$results = $this->_related[$name];
			if ($exists) {
				$this->_related[$name] = $save;
			}
			else
			{
				unset($this->_related[$name]);
			}
			return $results;
		} else
		{
			return $this->_related[$name];
		}
	}

	/**
	 * Returns a value indicating whether the named related object(s) has been loaded.
	 * @param string $name the relation name
	 * @return boolean a value indicating whether the named related object(s) has been loaded.
	 */
	public function hasRelated($name)
	{
		return isset($this->_related[$name]) || array_key_exists($name, $this->_related);
	}

	/**
	 * Returns the list of all attribute names of the model.
	 * This would return all column names of the table associated with this AR class.
	 * @return array list of attribute names.
	 */
	public function attributeNames()
	{
		return array_keys($this->getMetaData()->columns);
	}

	/**
	 * Returns the text label for the specified attribute.
	 * This method overrides the parent implementation by supporting
	 * returning the label defined in relational object.
	 * In particular, if the attribute name is in the form of "post.author.name",
	 * then this method will derive the label from the "author" relation's "name" attribute.
	 * @param string $attribute the attribute name
	 * @return string the attribute label
	 * @see generateAttributeLabel
	 */
	public function getAttributeLabel($attribute)
	{
		$labels = $this->attributeLabels();
		if (isset($labels[$attribute])) {
			return $labels[$attribute];
		} elseif (strpos($attribute, '.') !== false) {
			$segs = explode('.', $attribute);
			$name = array_pop($segs);
			$model = $this;
			foreach ($segs as $seg) {
				$relations = $model->getMetaData()->relations;
				if (isset($relations[$seg])) {
					$model = ActiveRecord::model($relations[$seg]->className);
				} else {
					break;
				}
			}
			return $model->getAttributeLabel($name);
		} else {
			return $this->generateAttributeLabel($attribute);
		}
	}

	/**
	 * Returns the named relation declared for this AR class.
	 * @param string $name the relation name
	 * @return CActiveRelation the named relation declared for this AR class. Null if the relation does not exist.
	 */
	public function getActiveRelation($name)
	{
		return isset($this->getMetaData()->relations[$name]) ? $this->getMetaData()->relations[$name] : null;
	}

	/**
	 * Returns the metadata of the table that this AR belongs to
	 * @return CDbTableSchema the metadata of the table that this AR belongs to
	 */
	public function getTableSchema()
	{
		return $this->getMetaData()->tableSchema;
	}

	/**
	 * Checks whether this AR has the named attribute
	 * @param string $name attribute name
	 * @return boolean whether this AR has the named attribute (table column).
	 */
	public function hasAttribute($name)
	{
		return isset($this->getMetaData()->columns[$name]);
	}

	/**
	 * Returns the named attribute value.
	 * If this is a new record and the attribute is not set before,
	 * the default column value will be returned.
	 * If this record is the result of a query and the attribute is not loaded,
	 * null will be returned.
	 * You may also use $this->AttributeName to obtain the attribute value.
	 * @param string $name the attribute name
	 * @return mixed the attribute value. Null if the attribute is not set or does not exist.
	 * @see hasAttribute
	 */
	public function getAttribute($name)
	{
		if (property_exists($this, $name)) {
			return $this->$name;
		}
		elseif (isset($this->_attributes[$name]))
		{
			return $this->_attributes[$name];
		}
	}

	/**
	 * Sets the named attribute value.
	 * You may also use $this->AttributeName to set the attribute value.
	 * @param string $name the attribute name
	 * @param mixed $value the attribute value.
	 * @return boolean whether the attribute exists and the assignment is conducted successfully
	 * @see hasAttribute
	 */
	public function setAttribute($name, $value)
	{
		if (property_exists($this, $name)) {
			$this->$name = $value;
		}
		elseif (isset($this->getMetaData()->table->columns[$name]))
		{
			$this->_attributes[$name] = $value;
		}
		else
		{
			return false;
		}
		return true;
	}

	/**
	 * Returns all column attribute values.
	 * Note, related objects are not returned.
	 * @param mixed $names names of attributes whose value needs to be returned.
	 * If this is true (default), then all attribute values will be returned, including
	 * those that are not loaded from DB (null will be returned for those attributes).
	 * If this is null, all attributes except those that are not loaded from DB will be returned.
	 * @return array attribute values indexed by attribute names.
	 */
	public function getAttributes($names = true)
	{
		$attributes = $this->_attributes;
		foreach ($this->getMetaData()->columns as $name => $column)
		{
			if (property_exists($this, $name)) {
				$attributes[$name] = $this->$name;
			}
			elseif ($names === true && !isset($attributes[$name]))
			{
				$attributes[$name] = null;
			}
		}
		if (is_array($names)) {
			$attrs = array();
			foreach ($names as $name)
			{
				if (property_exists($this, $name)) {
					$attrs[$name] = $this->$name;
				}
				else
				{
					$attrs[$name] = isset($attributes[$name]) ? $attributes[$name] : null;
				}
			}
			return $attrs;
		} else
		{
			return $attributes;
		}
	}

	/**
	 * Saves the current record.
	 *
	 * The record is inserted as a row into the database table if its {@link isNewRecord}
	 * property is true (usually the case when the record is created using the 'new'
	 * operator). Otherwise, it will be used to update the corresponding row in the table
	 * (usually the case if the record is obtained using one of those 'find' methods.)
	 *
	 * Validation will be performed before saving the record. If the validation fails,
	 * the record will not be saved. You can call {@link getErrors()} to retrieve the
	 * validation errors.
	 *
	 * If the record is saved via insertion, its {@link isNewRecord} property will be
	 * set false, and its {@link scenario} property will be set to be 'update'.
	 * And if its primary key is auto-incremental and is not set before insertion,
	 * the primary key will be populated with the automatically generated key value.
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
		else
		{
			return false;
		}
	}

	/**
	 * Returns if the current record is new.
	 * @return boolean whether the record is new and should be inserted when calling {@link save}.
	 * This property is automatically set in constructor and {@link populateRecord}.
	 * Defaults to false, but it will be set to true if the instance is created using
	 * the new operator.
	 */
	public function getIsNewRecord()
	{
		return $this->_new;
	}

	/**
	 * Sets if the record is new.
	 * @param boolean $value whether the record is new and should be inserted when calling {@link save}.
	 * @see getIsNewRecord
	 */
	public function setIsNewRecord($value)
	{
		$this->_new = $value;
	}

	/**
	 * This event is raised before the record is saved.
	 * By setting {@link CModelEvent::isValid} to be false, the normal {@link save()} process will be stopped.
	 * @param CModelEvent $event the event parameter
	 */
	public function onBeforeSave($event)
	{
		$this->raiseEvent('onBeforeSave', $event);
	}

	/**
	 * This event is raised after the record is saved.
	 * @param CEvent $event the event parameter
	 */
	public function onAfterSave($event)
	{
		$this->raiseEvent('onAfterSave', $event);
	}

	/**
	 * This event is raised before the record is deleted.
	 * By setting {@link CModelEvent::isValid} to be false, the normal {@link delete()} process will be stopped.
	 * @param CModelEvent $event the event parameter
	 */
	public function onBeforeDelete($event)
	{
		$this->raiseEvent('onBeforeDelete', $event);
	}

	/**
	 * This event is raised after the record is deleted.
	 * @param CEvent $event the event parameter
	 */
	public function onAfterDelete($event)
	{
		$this->raiseEvent('onAfterDelete', $event);
	}

	/**
	 * This method is invoked before saving a record (after validation, if any).
	 * The default implementation raises the {@link onBeforeSave} event.
	 * You may override this method to do any preparation work for record saving.
	 * Use {@link isNewRecord} to determine whether the saving is
	 * for inserting or updating record.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 * @return boolean whether the saving should be executed. Defaults to true.
	 */
	protected function beforeSave()
	{
		if ($this->hasEventHandler('onBeforeSave')) {
			$event = new CModelEvent($this);
			$this->onBeforeSave($event);
			return $event->isValid;
		} else
		{
			return true;
		}
	}

	/**
	 * This method is invoked after saving a record successfully.
	 * The default implementation raises the {@link onAfterSave} event.
	 * You may override this method to do postprocessing after record saving.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 */
	protected function afterSave()
	{
		if ($this->hasEventHandler('onAfterSave')) {
			$this->onAfterSave(new CEvent($this));
		}
	}

	/**
	 * This method is invoked before deleting a record.
	 * The default implementation raises the {@link onBeforeDelete} event.
	 * You may override this method to do any preparation work for record deletion.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 * @return boolean whether the record should be deleted. Defaults to true.
	 */
	protected function beforeDelete()
	{
		if ($this->hasEventHandler('onBeforeDelete')) {
			$event = new CModelEvent($this);
			$this->onBeforeDelete($event);
			return $event->isValid;
		} else
		{
			return true;
		}
	}

	/**
	 * This method is invoked after deleting a record.
	 * The default implementation raises the {@link onAfterDelete} event.
	 * You may override this method to do postprocessing after the record is deleted.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 */
	protected function afterDelete()
	{
		if ($this->hasEventHandler('onAfterDelete')) {
			$this->onAfterDelete(new CEvent($this));
		}
	}

	/**
	 * This method is invoked before an AR finder executes a find call.
	 * The find calls include {@link find}, {@link findAll}, {@link findByPk},
	 * {@link findAllByPk}, {@link findByAttributes} and {@link findAllByAttributes}.
	 * The default implementation raises the {@link onBeforeFind} event.
	 * If you override this method, make sure you call the parent implementation
	 * so that the event is raised properly.
	 *
	 * Starting from version 1.1.5, this method may be called with a hidden {@link CDbCriteria}
	 * parameter which represents the current query criteria as passed to a find method of AR.
	 */
	protected function beforeFind()
	{
		if ($this->hasEventHandler('onBeforeFind')) {
			$event = new CModelEvent($this);
			// for backward compatibility
			$event->criteria = func_num_args() > 0 ? func_get_arg(0) : null;
			$this->onBeforeFind($event);
		}
	}

	/**
	 * This method is invoked after each record is instantiated by a find method.
	 * The default implementation raises the {@link onAfterFind} event.
	 * You may override this method to do postprocessing after each newly found record is instantiated.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 */
	protected function afterFind()
	{
		if ($this->hasEventHandler('onAfterFind')) {
			$this->onAfterFind(new CEvent($this));
		}
	}

	/**
	 * Inserts a row into the table based on this active record attributes.
	 * If the table's primary key is auto-incremental and is null before insertion,
	 * it will be populated with the actual value after insertion.
	 * Note, validation is not performed in this method. You may call {@link validate} to perform the validation.
	 * After the record is inserted to DB successfully, its {@link isNewRecord} property will be set false,
	 * and its {@link scenario} property will be set to be 'update'.
	 * @param array $attributes list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved.
	 * @return boolean whether the attributes are valid and the record is inserted successfully.
	 * @throws CException if the record is not new
	 */
	public function insert($attributes = null)
	{
		if (!$this->getIsNewRecord()) {
			throw new Exception(Yii::t('yii', 'The active record cannot be inserted to database because it is not new.'));
		}
		if ($this->beforeSave()) {
			Yii::trace(get_class($this) . '.insert()', 'system.db.ar.ActiveRecord');
			$builder = $this->getCommandBuilder();
			$table = $this->getMetaData()->tableSchema;
			$command = $builder->createInsertCommand($table, $this->getAttributes($attributes));
			if ($command->execute()) {
				$primaryKey = $table->primaryKey;
				if ($table->sequenceName !== null) {
					if (is_string($primaryKey) && $this->$primaryKey === null) {
						$this->$primaryKey = $builder->getLastInsertID($table);
					}
					elseif (is_array($primaryKey))
					{
						foreach ($primaryKey as $pk)
						{
							if ($this->$pk === null) {
								$this->$pk = $builder->getLastInsertID($table);
								break;
							}
						}
					}
				}
				$this->_pk = $this->getPrimaryKey();
				$this->afterSave();
				$this->setIsNewRecord(false);
				$this->setScenario('update');
				return true;
			}
		}
		return false;
	}

	/**
	 * Updates the row represented by this active record.
	 * All loaded attributes will be saved to the database.
	 * Note, validation is not performed in this method. You may call {@link validate} to perform the validation.
	 * @param array $attributes list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved.
	 * @return boolean whether the update is successful
	 * @throws CException if the record is new
	 */
	public function update($attributes = null)
	{
		if ($this->getIsNewRecord()) {
			throw new Exception(Yii::t('yii', 'The active record cannot be updated because it is new.'));
		}
		if ($this->beforeSave()) {
			Yii::trace(get_class($this) . '.update()', 'system.db.ar.ActiveRecord');
			if ($this->_pk === null) {
				$this->_pk = $this->getPrimaryKey();
			}
			$this->updateByPk($this->getOldPrimaryKey(), $this->getAttributes($attributes));
			$this->_pk = $this->getPrimaryKey();
			$this->afterSave();
			return true;
		} else
		{
			return false;
		}
	}

	/**
	 * Saves a selected list of attributes.
	 * Unlike {@link save}, this method only saves the specified attributes
	 * of an existing row dataset and does NOT call either {@link beforeSave} or {@link afterSave}.
	 * Also note that this method does neither attribute filtering nor validation.
	 * So do not use this method with untrusted data (such as user posted data).
	 * You may consider the following alternative if you want to do so:
	 * <pre>
	 * $postRecord=Post::model()->findByPk($postID);
	 * $postRecord->attributes=$_POST['post'];
	 * $postRecord->save();
	 * </pre>
	 * @param array $attributes attributes to be updated. Each element represents an attribute name
	 * or an attribute value indexed by its name. If the latter, the record's
	 * attribute will be changed accordingly before saving.
	 * @return boolean whether the update is successful
	 * @throws CException if the record is new or any database error
	 */
	public function saveAttributes($attributes)
	{
		if (!$this->getIsNewRecord()) {
			Yii::trace(get_class($this) . '.saveAttributes()', 'system.db.ar.ActiveRecord');
			$values = array();
			foreach ($attributes as $name => $value)
			{
				if (is_integer($name)) {
					$values[$value] = $this->$value;
				}
				else
				{
					$values[$name] = $this->$name = $value;
				}
			}
			if ($this->_pk === null) {
				$this->_pk = $this->getPrimaryKey();
			}
			if ($this->updateByPk($this->getOldPrimaryKey(), $values) > 0) {
				$this->_pk = $this->getPrimaryKey();
				return true;
			} else
			{
				return false;
			}
		} else
		{
			throw new Exception(Yii::t('yii', 'The active record cannot be updated because it is new.'));
		}
	}

	/**
	 * Saves one or several counter columns for the current AR object.
	 * Note that this method differs from {@link updateCounters} in that it only
	 * saves the current AR object.
	 * An example usage is as follows:
	 * <pre>
	 * $postRecord=Post::model()->findByPk($postID);
	 * $postRecord->saveCounters(array('view_count'=>1));
	 * </pre>
	 * Use negative values if you want to decrease the counters.
	 * @param array $counters the counters to be updated (column name=>increment value)
	 * @return boolean whether the saving is successful
	 * @see updateCounters
	 */
	public function saveCounters($counters)
	{
		Yii::trace(get_class($this) . '.saveCounters()', 'system.db.ar.ActiveRecord');
		$builder = $this->getCommandBuilder();
		$table = $this->getTableSchema();
		$criteria = $builder->createPkCriteria($table, $this->getOldPrimaryKey());
		$command = $builder->createUpdateCounterCommand($this->getTableSchema(), $counters, $criteria);
		if ($command->execute()) {
			foreach ($counters as $name => $value)
			{
				$this->$name = $this->$name + $value;
			}
			return true;
		} else
		{
			return false;
		}
	}

	/**
	 * Deletes the row corresponding to this active record.
	 * @return boolean whether the deletion is successful.
	 * @throws CException if the record is new
	 */
	public function delete()
	{
		if (!$this->getIsNewRecord()) {
			Yii::trace(get_class($this) . '.delete()', 'system.db.ar.ActiveRecord');
			if ($this->beforeDelete()) {
				$result = $this->deleteByPk($this->getPrimaryKey()) > 0;
				$this->afterDelete();
				return $result;
			} else
			{
				return false;
			}
		} else
		{
			throw new Exception(Yii::t('yii', 'The active record cannot be deleted because it is new.'));
		}
	}

	/**
	 * Repopulates this active record with the latest data.
	 * @return boolean whether the row still exists in the database. If true, the latest data will be populated to this active record.
	 */
	public function refresh()
	{
		Yii::trace(get_class($this) . '.refresh()', 'system.db.ar.ActiveRecord');
		if (!$this->getIsNewRecord() && ($record = $this->findByPk($this->getPrimaryKey())) !== null) {
			$this->_attributes = array();
			$this->_related = array();
			foreach ($this->getMetaData()->columns as $name => $column)
			{
				if (property_exists($this, $name)) {
					$this->$name = $record->$name;
				}
				else
				{
					$this->_attributes[$name] = $record->$name;
				}
			}
			return true;
		} else
		{
			return false;
		}
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
	 * @return mixed the primary key value. An array (column name=>column value) is returned if the primary key is composite.
	 * If primary key is not defined, null will be returned.
	 */
	public function getPrimaryKey()
	{
		$table = static::getMetaData()->table;
		if (is_string($table->primaryKey)) {
			return $this->{$table->primaryKey};
		}
		elseif (is_array($table->primaryKey))
		{
			$values = array();
			foreach ($table->primaryKey as $name)
			{
				$values[$name] = $this->$name;
			}
			return $values;
		} else
		{
			return null;
		}
	}

	/**
	 * Sets the primary key value.
	 * After calling this method, the old primary key value can be obtained from {@link oldPrimaryKey}.
	 * @param mixed $value the new primary key value. If the primary key is composite, the new value
	 * should be provided as an array (column name=>column value).
	 */
	public function setPrimaryKey($value)
	{
		$this->_pk = $this->getPrimaryKey();
		$table = $this->getMetaData()->tableSchema;
		if (is_string($table->primaryKey)) {
			$this->{$table->primaryKey} = $value;
		}
		elseif (is_array($table->primaryKey))
		{
			foreach ($table->primaryKey as $name)
			{
				$this->$name = $value[$name];
			}
		}
	}

	/**
	 * Returns the old primary key value.
	 * This refers to the primary key value that is populated into the record
	 * after executing a find method (e.g. find(), findAll()).
	 * The value remains unchanged even if the primary key attribute is manually assigned with a different value.
	 * @return mixed the old primary key value. An array (column name=>column value) is returned if the primary key is composite.
	 * If primary key is not defined, null will be returned.
	 */
	public function getOldPrimaryKey()
	{
		return $this->_pk;
	}

	/**
	 * Sets the old primary key value.
	 * @param mixed $value the old primary key value.
	 */
	public function setOldPrimaryKey($value)
	{
		$this->_pk = $value;
	}

	/**
	 * Creates an active record with the given attributes.
	 * This method is internally used by the find methods.
	 *
	 * @param array $row attribute values (column name=>column value)
	 *
	 * @return ActiveRecord the newly created active record. The class of the object is the same as the model class.
	 * Null is returned if the input data is false.
	 */
	public static function populateRecord($row)
	{
		$record = static::instantiate($row);
		$record->setScenario('update');
		$columns = static::getMetaData()->table->columns;
		foreach ($row as $name => $value) {
			if (property_exists($record, $name)) {
				$record->$name = $value;
			} elseif (isset($columns[$name])) {
				$record->_attributes[$name] = $value;
			}
		}
		$record->_pk = $record->getPrimaryKey();
		return $record;
	}

	/**
	 * Creates an active record instance.
	 * This method is called by {@link populateRecord} and {@link populateRecords}.
	 * You may override this method if the instance being created
	 * depends the attributes that are to be populated to the record.
	 * For example, by creating a record based on the value of a column,
	 * you may implement the so-called single-table inheritance mapping.
	 * @param array $row list of attribute values for the active records.
	 * @return ActiveRecord the active record
	 */
	protected static function instantiate($row)
	{
		return static::newInstance();
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
}
