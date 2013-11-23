<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;
use yii\base\UnknownMethodException;
use yii\db\Exception;
use yii\db\TableSchema;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\StringHelper;

/**
 * ActiveRecord is the base class for classes representing relational data in terms of objects.
 *
 *
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
abstract class ActiveRecord extends \yii\db\ActiveRecord
{
	/**
	 * Returns the database connection used by this AR class.
	 * By default, the "elasticsearch" application component is used as the database connection.
	 * You may override this method if you want to use a different database connection.
	 * @return Connection the database connection used by this AR class.
	 */
	public static function getDb()
	{
		return \Yii::$app->getComponent('elasticsearch');
	}

	/**
	 * @inheritdoc
	 */
	public static function findBySql($sql, $params = array())
	{
		throw new NotSupportedException('findBySql() is not supported by elasticsearch ActiveRecord');
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
		// TODO add support for further options as described in http://www.elasticsearch.org/guide/reference/api/bulk/
		if (empty($attributes)) {
			return 0;
		}
		if (count($condition) != 1 || !isset($condition[reset(static::primaryKey())])) {
			throw new NotSupportedException('UpdateAll is only supported by primary key in elasticsearch.');
		}
		if (isset($attributes[reset(static::primaryKey())])) {
			throw new NotSupportedException('Updating the primary key is currently not supported by elasticsearch.');
		}
		$query = '';
		foreach((array) reset($condition) as $pk) {
			if (is_array($pk)) {
				$pk = reset($pk);
			}
			$action = Json::encode(array(
				"update" => array(
					"_id" => $pk,
					"_type" => static::indexType(),
					"_index" => static::indexName(),
				),
			));
			$data = Json::encode(array(
				"doc" => $attributes
			));
			$query .= $action . "\n" . $data . "\n";
			// TODO implement pk change
		}
		$url = '/' . static::indexName() . '/' . static::indexType() . '/_bulk';
		$response = static::getDb()->http()->post($url, array(), $query)->send();
		$body = Json::decode($response->getBody(true));
		$n=0;
		foreach($body['items'] as $item) {
			if ($item['update']['ok']) {
				$n++;
			}
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
		throw new NotSupportedException('Update Counters is not supported by elasticsearch.');
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
		// TODO use delete By Query feature
		// http://www.elasticsearch.org/guide/reference/api/delete-by-query/
		if (count($condition) != 1 || !isset($condition[reset(static::primaryKey())])) {
			throw new NotSupportedException('DeleteAll is only supported by primary key in elasticsearch.');
		}
		$query = '';
		foreach((array) reset($condition) as $pk) {
			if (is_array($pk)) {
				$pk = reset($pk);
			}
			$query .= Json::encode(array(
				"delete" => array(
					"_id" => $pk,
					"_type" => static::indexType(),
					"_index" => static::indexName(),
				),
			)) . "\n";
		}
		$url = '/' . static::indexName() . '/' . static::indexType() . '/_bulk';
		$response = static::getDb()->http()->post($url, array(), $query)->send();
		$body = Json::decode($response->getBody(true));
		$n=0;
		foreach($body['items'] as $item) {
			if ($item['delete']['ok']) {
				$n++;
			}
		}
		return $n;
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

	public static function primaryKey()
	{
		return array('id');
	}

	public static function columns()
	{
		return array('id' => 'integer');
	}

	public static function indexName()
	{
		return Inflector::pluralize(Inflector::camel2id(StringHelper::basename(get_called_class()), '-'));
	}

	public static function indexType()
	{
		return Inflector::camel2id(StringHelper::basename(get_called_class()), '-');
	}

	private static $_tables;
	/**
	 * Returns the schema information of the DB table associated with this AR class.
	 * @return TableSchema the schema information of the DB table associated with this AR class.
	 * @throws InvalidConfigException if the table for the AR class does not exist.
	 */
	public static function getTableSchema()
	{
		$class = get_called_class();
		if (isset(self::$_tables[$class])) {
			return self::$_tables[$class];
		}
		return self::$_tables[$class] = new TableSchema(array(
			'schemaName' => static::indexName(),
			'name' => static::indexType(),
			'primaryKey' => static::primaryKey(),
			'columns' => static::columns(),
		));
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
			$key = reset($this->primaryKey());
			$pk = $this->getAttribute($key);
			//unset($values[$key]);

			// save attributes
			if ($pk === null) {
				$url = '/' . static::indexName() . '/' . static::indexType();
				$request = $db->http()->post($url, array(), Json::encode($values));
			} else {
				$url = '/' . static::indexName() . '/' . static::indexType() . '/' . $pk;
				$request = $db->http()->put($url, array(), Json::encode($values));
			}
			$response = $request->send();
			$body = Json::decode($response->getBody(true));
			if (!$body['ok']) {
				return false;
			}
			$this->setOldAttributes($values);
			if ($pk === null) {
				$this->setAttribute($key, $body['_id']);
			}
			$this->afterSave(true);
			return true;
		}
		return false;
	}

	/**
	 * Returns a value indicating whether the specified operation is transactional in the current [[scenario]].
	 * This method will always return false as transactional operations are not supported by elasticsearch.
	 * @param integer $operation the operation to check. Possible values are [[OP_INSERT]], [[OP_UPDATE]] and [[OP_DELETE]].
	 * @return boolean whether the specified operation is transactional in the current [[scenario]].
	 */
	public function isTransactional($operation)
	{
		return false;
	}
}
