<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\db\TableSchema;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\StringHelper;

// TODO handle optimistic lock

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
	private $_id;
	private $_version;

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
	 * @inheritDoc
	 */
	public static function find($q = null)
	{
		$query = static::createQuery();
		if (is_array($q)) {
			if (count($q) == 1 && isset($q['primaryKey'])) {
				return static::get($q['primaryKey']);
			}
			return $query->where($q)->one();
		} elseif ($q !== null) {
			return static::get($q);
		}
		return $query;
	}

	public static function get($primaryKey, $options = [])
	{
		$command = static::getDb()->createCommand();
		$result = $command->get(static::index(), static::type(), $primaryKey, $options);
		if ($result['exists']) {
			return static::create($result);
		}
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public static function createQuery()
	{
		return new ActiveQuery(['modelClass' => get_called_class()]);
	}

	/**
	 * @inheritDoc
	 */
	public static function createActiveRelation($config = [])
	{
		return new ActiveRelation($config);
	}

	// TODO implement copy and move as pk change is not possible

	/**
	 * Sets the primary key
	 * @param mixed $value
	 * @throws \yii\base\InvalidCallException when record is not new
	 */
	public function setPrimaryKey($value)
	{
		if ($this->isNewRecord) {
			$this->_id = $value;
		} else {
			throw new InvalidCallException('Changing the primaryKey of an already saved record is not allowed.');
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getPrimaryKey($asArray = false)
	{
		if ($asArray) {
			return ['primaryKey' => $this->_id];
		} else {
			return $this->_id;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getOldPrimaryKey($asArray = false)
	{
		return $this->getPrimaryKey($asArray);
	}

	/**
	 * This method defines the primary.
	 *
	 * The primaryKey for elasticsearch documents is always `primaryKey`. It can not be changed.
	 *
	 * @return string[] the primary keys of this record.
	 */
	public static function primaryKey()
	{
		return ['primaryKey'];
	}

	/**
	 * Returns the list of all attribute names of the model.
	 * This method must be overridden by child classes to define available attributes.
	 * @return array list of attribute names.
	 */
	public static function attributes()
	{
		throw new InvalidConfigException('The attributes() method of elasticsearch ActiveRecord has to be implemented by child classes.');
	}

	// TODO index and type definition
	public static function index()
	{
		return Inflector::pluralize(Inflector::camel2id(StringHelper::basename(get_called_class()), '-'));
	}

	public static function type()
	{
		return Inflector::camel2id(StringHelper::basename(get_called_class()), '-');
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
		$row['_source']['primaryKey'] = $row['_id'];
		$record = parent::create($row['_source']);
		return $record;
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
			$values = $this->getDirtyAttributes($attributes);

			$response = static::getDb()->createCommand()->insert(
				static::index(),
				static::type(),
				$values,
				$this->getPrimaryKey()
			);

			if (!$response['ok']) {
				return false;
			}
			$this->_id = $response['_id'];
			$this->_version = $response['_version'];
			$this->setOldAttributes($values);
			$this->afterSave(true);
			return true;
		}
		return false;
	}

	/**
	 * Updates all records whos primary keys are given.
	 * For example, to change the status to be 1 for all customers whose status is 2:
	 *
	 * ~~~
	 * Customer::updateAll(array('status' => 1), array(2, 3, 4));
	 * ~~~
	 *
	 * @param array $attributes attribute values (name-value pairs) to be saved into the table
	 * @param array $condition the conditions that will be put in the WHERE part of the UPDATE SQL.
	 * Please refer to [[ActiveQuery::where()]] on how to specify this parameter.
	 * @param array $params this parameter is ignored in redis implementation.
	 * @return integer the number of rows updated
	 */
	public static function updateAll($attributes, $condition = [], $params = [])
	{
		if (empty($condition)) {
			return 0;
		}
		$bulk = '';
		foreach((array) $condition as $pk) {
			$action = Json::encode([
				"update" => [
					"_id" => $pk,
					"_type" => static::type(),
					"_index" => static::index(),
				],
			]);
			$data = Json::encode(array(
				"doc" => $attributes
			));
			$bulk .= $action . "\n" . $data . "\n";
		}

		// TODO do this via command
		$url = '/' . static::index() . '/' . static::type() . '/_bulk';
		$response = static::getDb()->http()->post($url, null, $bulk)->send();
		$body = Json::decode($response->getBody(true));
		$n=0;
		foreach($body['items'] as $item) {
			if ($item['update']['ok']) {
				$n++;
			}
			// TODO might want to update the _version in update()
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
	public static function deleteAll($condition = [], $params = [])
	{
		if (empty($condition)) {
			return 0;
		}
		$bulk = '';
		foreach((array) $condition as $pk) {
			$bulk = Json::encode([
				"delete" => [
					"_id" => $pk,
					"_type" => static::type(),
					"_index" => static::index(),
				],
			]) . "\n";
		}

		// TODO do this via command
		$url = '/' . static::index() . '/' . static::type() . '/_bulk';
		$response = static::getDb()->http()->post($url, null, $bulk)->send();
		$body = Json::decode($response->getBody(true));
		$n=0;
		foreach($body['items'] as $item) {
			if ($item['delete']['ok']) {
				$n++;
			}
			// TODO might want to update the _version in update()
		}
		return $n;
	}

	/**
	 * @inheritdoc
	 */
	public static function updateAllCounters($counters, $condition = null, $params = [])
	{
		throw new NotSupportedException('Update Counters is not supported by elasticsearch ActiveRecord.');
	}

	/**
	 * @inheritdoc
	 */
	public static function getTableSchema()
	{
		throw new NotSupportedException('getTableSchema() is not supported by elasticsearch ActiveRecord.');
	}

	/**
	 * @inheritDoc
	 */
	public static function tableName()
	{
		return static::index() . '/' . static::type();
	}

	/**
	 * @inheritdoc
	 */
	public static function findBySql($sql, $params = [])
	{
		throw new NotSupportedException('findBySql() is not supported by elasticsearch ActiveRecord.');
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
