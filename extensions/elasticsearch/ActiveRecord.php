<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\StringHelper;

/**
 * ActiveRecord is the base class for classes representing relational data in terms of objects.
 *
 * This class implements the ActiveRecord pattern for the fulltext search and data storage
 * [elasticsearch](http://www.elasticsearch.org/).
 *
 * For defining a record a subclass should at least implement the [[attributes()]] method to define
 * attributes.
 * The primary key (the `_id` field in elasticsearch terms) is represented by `getId()` and `setId()`.
 * The primary key is not part of the attributes.
 *
 * The following is an example model called `Customer`:
 *
 * ```php
 * class Customer extends \yii\elasticsearch\ActiveRecord
 * {
 *     public function attributes()
 *     {
 *         return ['id', 'name', 'address', 'registration_date'];
 *     }
 * }
 * ```
 *
 * You may override [[index()]] and [[type()]] to define the index and type this record represents.
 *
 * @property float $score Returns the score of this record when it was retrieved via a [[find()]] query. This
 * property is read-only.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ActiveRecord extends BaseActiveRecord
{
    private $_id;
    private $_score;
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
     * @inheritdoc
     */
    public static function find($q = null)
    {
        $query = static::createQuery();
        if (is_array($q)) {
            return $query->andWhere($q)->one();
        } elseif ($q !== null) {
            return static::get($q);
        }

        return $query;
    }

    /**
     * Gets a record by its primary key.
     *
     * @param  mixed       $primaryKey the primaryKey value
     * @param  array       $options    options given in this parameter are passed to elasticsearch
     *                                 as request URI parameters.
     *                                 Please refer to the [elasticsearch documentation](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-get.html)
     *                                 for more details on these options.
     * @return static|null The record instance or null if it was not found.
     */
    public static function get($primaryKey, $options = [])
    {
        if ($primaryKey === null) {
            return null;
        }
        $command = static::getDb()->createCommand();
        $result = $command->get(static::index(), static::type(), $primaryKey, $options);
        if ($result['exists']) {
            $model = static::instantiate($result);
            static::populateRecord($model, $result);
            $model->afterFind();

            return $model;
        }

        return null;
    }

    /**
     * Gets a list of records by its primary keys.
     *
     * @param array $primaryKeys an array of primaryKey values
     * @param array $options     options given in this parameter are passed to elasticsearch
     *                           as request URI parameters.
     *
     * Please refer to the [elasticsearch documentation](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-get.html)
     * for more details on these options.
     * @return static|null The record instance or null if it was not found.
     */

    public static function mget($primaryKeys, $options = [])
    {
        if (empty($primaryKeys)) {
            return [];
        }
        $command = static::getDb()->createCommand();
        $result = $command->mget(static::index(), static::type(), $primaryKeys, $options);
        $models = [];
        foreach ($result['docs'] as $doc) {
            if ($doc['exists']) {
                $model = static::instantiate($doc);
                static::populateRecord($model, $doc);
                $model->afterFind();
                $models[] = $model;
            }
        }

        return $models;
    }

    // TODO add more like this feature http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-more-like-this.html

    // TODO add percolate functionality http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-percolate.html

    /**
     * Creates an [[ActiveQuery]] instance.
     *
     * This method is called by [[find()]], [[findBySql()]] to start a SELECT query but also
     * by [[hasOne()]] and [[hasMany()]] to create a relational query.
     * You may override this method to return a customized query (e.g. `CustomerQuery` specified
     * written for querying `Customer` purpose.)
     *
     * You may also define default conditions that should apply to all queries unless overridden:
     *
     * ```php
     * public static function createQuery($config = [])
     * {
     *     return parent::createQuery($config)->where(['deleted' => false]);
     * }
     * ```
     *
     * Note that all queries should use [[Query::andWhere()]] and [[Query::orWhere()]] to keep the
     * default condition. Using [[Query::where()]] will override the default condition.
     *
     * @param  array       $config the configuration passed to the ActiveQuery class.
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function createQuery($config = [])
    {
        $config['modelClass'] = get_called_class();

        return new ActiveQuery($config);
    }

    // TODO implement copy and move as pk change is not possible

    /**
     * @return float returns the score of this record when it was retrieved via a [[find()]] query.
     */
    public function getScore()
    {
        return $this->_score;
    }

    /**
     * Sets the primary key
     * @param  mixed                          $value
     * @throws \yii\base\InvalidCallException when record is not new
     */
    public function setPrimaryKey($value)
    {
        $pk = static::primaryKey()[0];
        if ($this->getIsNewRecord() || $pk != '_id') {
            $this->$pk = $value;
        } else {
            throw new InvalidCallException('Changing the primaryKey of an already saved record is not allowed.');
        }
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKey($asArray = false)
    {
        $pk = static::primaryKey()[0];
        if ($asArray) {
            return [$pk => $this->$pk];
        } else {
            return $this->$pk;
        }
    }

    /**
     * @inheritdoc
     */
    public function getOldPrimaryKey($asArray = false)
    {
        $pk = static::primaryKey()[0];
        if ($this->getIsNewRecord()) {
            $id = null;
        } elseif ($pk == '_id') {
            $id = $this->_id;
        } else {
            $id = $this->getOldAttribute($pk);
        }
        if ($asArray) {
            return [$pk => $id];
        } else {
            return $id;
        }
    }

    /**
     * This method defines the attribute that uniquely identifies a record.
     *
     * The primaryKey for elasticsearch documents is the `_id` field by default. This field is not part of the
     * ActiveRecord attributes so you should never add `_id` to the list of [[attributes()|attributes]].
     *
     * You may overide this method to define the primary key name when you have defined
     * [path mapping](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/mapping-id-field.html)
     * for the `_id` field so that it is part of the `_source` and thus part of the [[attributes()|attributes]].
     *
     * Note that elasticsearch only supports _one_ attribute to be the primary key. However to match the signature
     * of the [[\yii\db\ActiveRecordInterface|ActiveRecordInterface]] this methods returns an array instead of a
     * single string.
     *
     * @return string[] array of primary key attributes. Only the first element of the array will be used.
     */
    public static function primaryKey()
    {
        return ['_id'];
    }

    /**
     * Returns the list of all attribute names of the model.
     *
     * This method must be overridden by child classes to define available attributes.
     *
     * Attributes are names of fields of the corresponding elasticsearch document.
     * The primaryKey for elasticsearch documents is the `_id` field by default which is not part of the attributes.
     * You may define [path mapping](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/mapping-id-field.html)
     * for the `_id` field so that it is part of the `_source` fields and thus becomes part of the attributes.
     *
     * @return string[] list of attribute names.
     */
    public function attributes()
    {
        throw new InvalidConfigException('The attributes() method of elasticsearch ActiveRecord has to be implemented by child classes.');
    }

    /**
     * @return string the name of the index this record is stored in.
     */
    public static function index()
    {
        return Inflector::pluralize(Inflector::camel2id(StringHelper::basename(get_called_class()), '-'));
    }

    /**
     * @return string the name of the type of this record.
     */
    public static function type()
    {
        return Inflector::camel2id(StringHelper::basename(get_called_class()), '-');
    }

    /**
     * @inheritdoc
     */
    public static function populateRecord($record, $row)
    {
        parent::populateRecord($record, $row['_source']);
        $pk = static::primaryKey()[0];
        if ($pk === '_id') {
            $record->_id = $row['_id'];
        }
        $record->_score = isset($row['_score']) ? $row['_score'] : null;
        $record->_version = isset($row['_version']) ? $row['_version'] : null; // TODO version should always be available...
    }

    /**
     * Creates an active record instance.
     *
     * This method is called together with [[populateRecord()]] by [[ActiveQuery]].
     * It is not meant to be used for creating new records directly.
     *
     * You may override this method if the instance being created
     * depends on the row data to be populated into the record.
     * For example, by creating a record based on the value of a column,
     * you may implement the so-called single-table inheritance mapping.
     * @param  array  $row row data to be populated into the record.
     *                     This array consists of the following keys:
     *                     - `_source`: refers to the attributes of the record.
     *                     - `_type`: the type this record is stored in.
     *                     - `_index`: the index this record is stored in.
     * @return static the newly created active record
     */
    public static function instantiate($row)
    {
        return new static;
    }

    /**
     * Inserts a document into the associated index using the attribute values of this record.
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
     * If the [[primaryKey|primary key]] is not set (null) during insertion,
     * it will be populated with a
     * [randomly generated value](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-index_.html#_automatic_id_generation)
     * after insertion.
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
     *                               If the validation fails, the record will not be inserted into the database.
     * @param array   $attributes    list of attributes that need to be saved. Defaults to null,
     *                               meaning all attributes will be saved.
     * @param array   $options       options given in this parameter are passed to elasticsearch
     *                               as request URI parameters. These are among others:
     *
     * - `routing` define shard placement of this record.
     * - `parent` by giving the primaryKey of another record this defines a parent-child relation
     * - `timestamp` specifies the timestamp to store along with the document. Default is indexing time.
     *
     * Please refer to the [elasticsearch documentation](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-index_.html)
     * for more details on these options.
     *
     * By default the `op_type` is set to `create`.
     * @return boolean whether the attributes are valid and the record is inserted successfully.
     */
    public function insert($runValidation = true, $attributes = null, $options = ['op_type' => 'create'])
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
                $this->getPrimaryKey(),
                $options
            );

            if (!isset($response['ok'])) {
                return false;
            }
            $pk = static::primaryKey()[0];
            $this->$pk = $response['_id'];
            if ($pk != '_id') {
                $values[$pk] = $response['_id'];
            }
            $this->_version = $response['_version'];
            $this->_score = null;
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
     * Customer::updateAll(['status' => 1], [2, 3, 4]);
     * ~~~
     *
     * @param  array   $attributes attribute values (name-value pairs) to be saved into the table
     * @param  array   $condition  the conditions that will be put in the WHERE part of the UPDATE SQL.
     *                             Please refer to [[ActiveQuery::where()]] on how to specify this parameter.
     * @return integer the number of rows updated
     */
    public static function updateAll($attributes, $condition = [])
    {
        $pkName = static::primaryKey()[0];
        if (count($condition) == 1 && isset($condition[$pkName])) {
            $primaryKeys = is_array($condition[$pkName]) ? $condition[$pkName] : [$condition[$pkName]];
        } else {
            $primaryKeys = static::find()->where($condition)->column($pkName); // TODO check whether this works with default pk _id
        }
        if (empty($primaryKeys)) {
            return 0;
        }
        $bulk = '';
        foreach ($primaryKeys as $pk) {
            $action = Json::encode([
                "update" => [
                    "_id" => $pk,
                    "_type" => static::type(),
                    "_index" => static::index(),
                ],
            ]);
            $data = Json::encode([
                "doc" => $attributes
            ]);
            $bulk .= $action . "\n" . $data . "\n";
        }

        // TODO do this via command
        $url = [static::index(), static::type(), '_bulk'];
        $response = static::getDb()->post($url, [], $bulk);
        $n = 0;
        $errors = [];
        foreach ($response['items'] as $item) {
            if (isset($item['update']['error'])) {
                $errors[] = $item['update'];
            } elseif ($item['update']['ok']) {
                $n++;
            }
        }
        if (!empty($errors)) {
            throw new Exception(__METHOD__ . ' failed updating records.', $errors);
        }

        return $n;
    }

    /**
     * Updates all matching records using the provided counter changes and conditions.
     * For example, to increment all customers' age by 1,
     *
     * ~~~
     * Customer::updateAllCounters(['age' => 1]);
     * ~~~
     *
     * @param  array        $counters  the counters to be updated (attribute name => increment value).
     *                                 Use negative values if you want to decrement the counters.
     * @param  string|array $condition the conditions that will be put in the WHERE part of the UPDATE SQL.
     *                                 Please refer to [[Query::where()]] on how to specify this parameter.
     * @return integer      the number of rows updated
     */
    public static function updateAllCounters($counters, $condition = [])
    {
        $pkName = static::primaryKey()[0];
        if (count($condition) == 1 && isset($condition[$pkName])) {
            $primaryKeys = is_array($condition[$pkName]) ? $condition[$pkName] : [$condition[$pkName]];
        } else {
            $primaryKeys = static::find()->where($condition)->column($pkName); // TODO check whether this works with default pk _id
        }
        if (empty($primaryKeys) || empty($counters)) {
            return 0;
        }
        $bulk = '';
        foreach ($primaryKeys as $pk) {
            $action = Json::encode([
                "update" => [
                    "_id" => $pk,
                    "_type" => static::type(),
                    "_index" => static::index(),
                ],
            ]);
            $script = '';
            foreach ($counters as $counter => $value) {
                $script .= "ctx._source.$counter += $counter;\n";
            }
            $data = Json::encode([
                "script" => $script,
                "params" => $counters
            ]);
            $bulk .= $action . "\n" . $data . "\n";
        }

        // TODO do this via command
        $url = [static::index(), static::type(), '_bulk'];
        $response = static::getDb()->post($url, [], $bulk);
        $n = 0;
        $errors = [];
        foreach ($response['items'] as $item) {
            if (isset($item['update']['error'])) {
                $errors[] = $item['update'];
            } elseif ($item['update']['ok']) {
                $n++;
            }
        }
        if (!empty($errors)) {
            throw new Exception(__METHOD__ . ' failed updating records counters.', $errors);
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
     * @param  array   $condition the conditions that will be put in the WHERE part of the DELETE SQL.
     *                            Please refer to [[ActiveQuery::where()]] on how to specify this parameter.
     * @return integer the number of rows deleted
     */
    public static function deleteAll($condition = [])
    {
        $pkName = static::primaryKey()[0];
        if (count($condition) == 1 && isset($condition[$pkName])) {
            $primaryKeys = is_array($condition[$pkName]) ? $condition[$pkName] : [$condition[$pkName]];
        } else {
            $primaryKeys = static::find()->where($condition)->column($pkName); // TODO check whether this works with default pk _id
        }
        if (empty($primaryKeys)) {
            return 0;
        }
        $bulk = '';
        foreach ($primaryKeys as $pk) {
            $bulk .= Json::encode([
                "delete" => [
                    "_id" => $pk,
                    "_type" => static::type(),
                    "_index" => static::index(),
                ],
            ]) . "\n";
        }

        // TODO do this via command
        $url = [static::index(), static::type(), '_bulk'];
        $response = static::getDb()->post($url, [], $bulk);
        $n = 0;
        $errors = [];
        foreach ($response['items'] as $item) {
            if (isset($item['delete']['error'])) {
                $errors[] = $item['delete'];
            } elseif ($item['delete']['found'] && $item['delete']['ok']) {
                $n++;
            }
        }
        if (!empty($errors)) {
            throw new Exception(__METHOD__ . ' failed deleting records.', $errors);
        }

        return $n;
    }
}
