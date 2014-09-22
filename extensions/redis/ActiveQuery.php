<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\redis;

use yii\base\Component;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveQueryTrait;
use yii\db\ActiveRelationTrait;
use yii\db\QueryTrait;

/**
 * ActiveQuery represents a query associated with an Active Record class.
 *
 * An ActiveQuery can be a normal query or be used in a relational context.
 *
 * ActiveQuery instances are usually created by [[ActiveRecord::find()]].
 * Relational queries are created by [[ActiveRecord::hasOne()]] and [[ActiveRecord::hasMany()]].
 *
 * Normal Query
 * ------------
 *
 * ActiveQuery mainly provides the following methods to retrieve the query results:
 *
 * - [[one()]]: returns a single record populated with the first row of data.
 * - [[all()]]: returns all records based on the query results.
 * - [[count()]]: returns the number of records.
 * - [[sum()]]: returns the sum over the specified column.
 * - [[average()]]: returns the average over the specified column.
 * - [[min()]]: returns the min over the specified column.
 * - [[max()]]: returns the max over the specified column.
 * - [[scalar()]]: returns the value of the first column in the first row of the query result.
 * - [[exists()]]: returns a value indicating whether the query result has data or not.
 *
 * You can use query methods, such as [[where()]], [[limit()]] and [[orderBy()]] to customize the query options.
 *
 * ActiveQuery also provides the following additional query options:
 *
 * - [[with()]]: list of relations that this query should be performed with.
 * - [[indexBy()]]: the name of the column by which the query result should be indexed.
 * - [[asArray()]]: whether to return each record as an array.
 *
 * These options can be configured using methods of the same name. For example:
 *
 * ```php
 * $customers = Customer::find()->with('orders')->asArray()->all();
 * ```
 *
 * Relational query
 * ----------------
 *
 * In relational context ActiveQuery represents a relation between two Active Record classes.
 *
 * Relational ActiveQuery instances are usually created by calling [[ActiveRecord::hasOne()]] and
 * [[ActiveRecord::hasMany()]]. An Active Record class declares a relation by defining
 * a getter method which calls one of the above methods and returns the created ActiveQuery object.
 *
 * A relation is specified by [[link]] which represents the association between columns
 * of different tables; and the multiplicity of the relation is indicated by [[multiple]].
 *
 * If a relation involves a pivot table, it may be specified by [[via()]].
 * This methods may only be called in a relational context. Same is true for [[inverseOf()]], which
 * marks a relation as inverse of another relation.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ActiveQuery extends Component implements ActiveQueryInterface
{
    use QueryTrait;
    use ActiveQueryTrait;
    use ActiveRelationTrait;

    /**
     * @event Event an event that is triggered when the query is initialized via [[init()]].
     */
    const EVENT_INIT = 'init';


    /**
     * Constructor.
     * @param array $modelClass the model class associated with this query
     * @param array $config configurations to be applied to the newly created query object
     */
    public function __construct($modelClass, $config = [])
    {
        $this->modelClass = $modelClass;
        parent::__construct($config);
    }

    /**
     * Initializes the object.
     * This method is called at the end of the constructor. The default implementation will trigger
     * an [[EVENT_INIT]] event. If you override this method, make sure you call the parent implementation at the end
     * to ensure triggering of the event.
     */
    public function init()
    {
        parent::init();
        $this->trigger(self::EVENT_INIT);
    }

    /**
     * Executes the query and returns all results as an array.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `db` application component will be used.
     * @return array|ActiveRecord[] the query results. If the query results in nothing, an empty array will be returned.
     */
    public function all($db = null)
    {
        // TODO add support for orderBy
        $data = $this->executeScript($db, 'All');
        $rows = [];
        foreach ($data as $dataRow) {
            $row = [];
            $c = count($dataRow);
            for ($i = 0; $i < $c;) {
                $row[$dataRow[$i++]] = $dataRow[$i++];
            }

            $rows[] = $row;
        }
        if (!empty($rows)) {
            $models = $this->createModels($rows);
            if (!empty($this->with)) {
                $this->findWith($this->with, $models);
            }
            if (!$this->asArray) {
                foreach ($models as $model) {
                    $model->afterFind();
                }
            }

            return $models;
        } else {
            return [];
        }
    }

    /**
     * Executes the query and returns a single row of result.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `db` application component will be used.
     * @return ActiveRecord|array|null a single row of query result. Depending on the setting of [[asArray]],
     * the query result may be either an array or an ActiveRecord object. Null will be returned
     * if the query results in nothing.
     */
    public function one($db = null)
    {
        // TODO add support for orderBy
        $data = $this->executeScript($db, 'One');
        if (empty($data)) {
            return null;
        }
        $row = [];
        $c = count($data);
        for ($i = 0; $i < $c;) {
            $row[$data[$i++]] = $data[$i++];
        }
        if ($this->asArray) {
            $model = $row;
        } else {
            /* @var $class ActiveRecord */
            $class = $this->modelClass;
            $model = $class::instantiate($row);
            $class::populateRecord($model, $row);
        }
        if (!empty($this->with)) {
            $models = [$model];
            $this->findWith($this->with, $models);
            $model = $models[0];
        }
        if (!$this->asArray) {
            $model->afterFind();
        }

        return $model;
    }

    /**
     * Returns the number of records.
     * @param string $q the COUNT expression. This parameter is ignored by this implementation.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `db` application component will be used.
     * @return integer number of records
     */
    public function count($q = '*', $db = null)
    {
        if ($this->where === null) {
            /* @var $modelClass ActiveRecord */
            $modelClass = $this->modelClass;
            if ($db === null) {
                $db = $modelClass::getDb();
            }

            return $db->executeCommand('LLEN', [$modelClass::keyPrefix()]);
        } else {
            return $this->executeScript($db, 'Count');
        }
    }

    /**
     * Returns a value indicating whether the query result contains any row of data.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `db` application component will be used.
     * @return boolean whether the query result contains any row of data.
     */
    public function exists($db = null)
    {
        return $this->one($db) !== null;
    }

    /**
     * Executes the query and returns the first column of the result.
     * @param string $column name of the column to select
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `db` application component will be used.
     * @return array the first column of the query result. An empty array is returned if the query results in nothing.
     */
    public function column($column, $db = null)
    {
        // TODO add support for orderBy
        return $this->executeScript($db, 'Column', $column);
    }

    /**
     * Returns the number of records.
     * @param string $column the column to sum up
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `db` application component will be used.
     * @return integer number of records
     */
    public function sum($column, $db = null)
    {
        return $this->executeScript($db, 'Sum', $column);
    }

    /**
     * Returns the average of the specified column values.
     * @param string $column the column name or expression.
     * Make sure you properly quote column names in the expression.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `db` application component will be used.
     * @return integer the average of the specified column values.
     */
    public function average($column, $db = null)
    {
        return $this->executeScript($db, 'Average', $column);
    }

    /**
     * Returns the minimum of the specified column values.
     * @param string $column the column name or expression.
     * Make sure you properly quote column names in the expression.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `db` application component will be used.
     * @return integer the minimum of the specified column values.
     */
    public function min($column, $db = null)
    {
        return $this->executeScript($db, 'Min', $column);
    }

    /**
     * Returns the maximum of the specified column values.
     * @param string $column the column name or expression.
     * Make sure you properly quote column names in the expression.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `db` application component will be used.
     * @return integer the maximum of the specified column values.
     */
    public function max($column, $db = null)
    {
        return $this->executeScript($db, 'Max', $column);
    }

    /**
     * Returns the query result as a scalar value.
     * The value returned will be the specified attribute in the first record of the query results.
     * @param string $attribute name of the attribute to select
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `db` application component will be used.
     * @return string the value of the specified attribute in the first record of the query result.
     * Null is returned if the query result is empty.
     */
    public function scalar($attribute, $db = null)
    {
        $record = $this->one($db);
        if ($record !== null) {
            return $record->hasAttribute($attribute) ? $record->$attribute : null;
        } else {
            return null;
        }
    }

    /**
     * Executes a script created by [[LuaScriptBuilder]]
     * @param Connection|null $db the database connection used to execute the query.
     * If this parameter is not given, the `db` application component will be used.
     * @param string $type the type of the script to generate
     * @param string $columnName
     * @return array|bool|null|string
     */
    protected function executeScript($db, $type, $columnName = null)
    {
        if ($this->primaryModel !== null) {
            // lazy loading
            if ($this->via instanceof self) {
                // via pivot table
                $viaModels = $this->via->findPivotRows([$this->primaryModel]);
                $this->filterByModels($viaModels);
            } elseif (is_array($this->via)) {
                // via relation
                /* @var $viaQuery ActiveQuery */
                list($viaName, $viaQuery) = $this->via;
                if ($viaQuery->multiple) {
                    $viaModels = $viaQuery->all();
                    $this->primaryModel->populateRelation($viaName, $viaModels);
                } else {
                    $model = $viaQuery->one();
                    $this->primaryModel->populateRelation($viaName, $model);
                    $viaModels = $model === null ? [] : [$model];
                }
                $this->filterByModels($viaModels);
            } else {
                $this->filterByModels([$this->primaryModel]);
            }
        }

        if (!empty($this->orderBy)) {
            throw new NotSupportedException('orderBy is currently not supported by redis ActiveRecord.');
        }

        /* @var $modelClass ActiveRecord */
        $modelClass = $this->modelClass;

        if ($db === null) {
            $db = $modelClass::getDb();
        }

        // find by primary key if possible. This is much faster than scanning all records
        if (is_array($this->where) && !isset($this->where[0]) && $modelClass::isPrimaryKey(array_keys($this->where))) {
            return $this->findByPk($db, $type, $columnName);
        }

        $method = 'build' . $type;
        $script = $db->getLuaScriptBuilder()->$method($this, $columnName);

        return $db->executeCommand('EVAL', [$script, 0]);
    }

    /**
     * Fetch by pk if possible as this is much faster
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `db` application component will be used.
     * @param string $type the type of the script to generate
     * @param string $columnName
     * @return array|bool|null|string
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\NotSupportedException
     */
    private function findByPk($db, $type, $columnName = null)
    {
        if (count($this->where) == 1) {
            $pks = (array) reset($this->where);
        } else {
            foreach ($this->where as $values) {
                if (is_array($values)) {
                    // TODO support composite IN for composite PK
                    throw new NotSupportedException('Find by composite PK is not supported by redis ActiveRecord.');
                }
            }
            $pks = [$this->where];
        }

        /* @var $modelClass ActiveRecord */
        $modelClass = $this->modelClass;

        if ($type == 'Count') {
            $start = 0;
            $limit = null;
        } else {
            $start = $this->offset === null ? 0 : $this->offset;
            $limit = $this->limit;
        }
        $i = 0;
        $data = [];
        foreach ($pks as $pk) {
            if (++$i > $start && ($limit === null || $i <= $start + $limit)) {
                $key = $modelClass::keyPrefix() . ':a:' . $modelClass::buildKey($pk);
                $result = $db->executeCommand('HGETALL', [$key]);
                if (!empty($result)) {
                    $data[] = $result;
                    if ($type === 'One' && $this->orderBy === null) {
                        break;
                    }
                }
            }
        }
        // TODO support orderBy

        switch ($type) {
            case 'All':
                return $data;
            case 'One':
                return reset($data);
            case 'Count':
                return count($data);
            case 'Column':
                $column = [];
                foreach ($data as $dataRow) {
                    $row = [];
                    $c = count($dataRow);
                    for ($i = 0; $i < $c;) {
                        $row[$dataRow[$i++]] = $dataRow[$i++];
                    }
                    $column[] = $row[$columnName];
                }

                return $column;
            case 'Sum':
                $sum = 0;
                foreach ($data as $dataRow) {
                    $c = count($dataRow);
                    for ($i = 0; $i < $c;) {
                        if ($dataRow[$i++] == $columnName) {
                            $sum += $dataRow[$i];
                            break;
                        }
                    }
                }

                return $sum;
            case 'Average':
                $sum = 0;
                $count = 0;
                foreach ($data as $dataRow) {
                    $count++;
                    $c = count($dataRow);
                    for ($i = 0; $i < $c;) {
                        if ($dataRow[$i++] == $columnName) {
                            $sum += $dataRow[$i];
                            break;
                        }
                    }
                }

                return $sum / $count;
            case 'Min':
                $min = null;
                foreach ($data as $dataRow) {
                    $c = count($dataRow);
                    for ($i = 0; $i < $c;) {
                        if ($dataRow[$i++] == $columnName && ($min == null || $dataRow[$i] < $min)) {
                            $min = $dataRow[$i];
                            break;
                        }
                    }
                }

                return $min;
            case 'Max':
                $max = null;
                foreach ($data as $dataRow) {
                    $c = count($dataRow);
                    for ($i = 0; $i < $c;) {
                        if ($dataRow[$i++] == $columnName && ($max == null || $dataRow[$i] > $max)) {
                            $max = $dataRow[$i];
                            break;
                        }
                    }
                }

                return $max;
        }
        throw new InvalidParamException('Unknown fetch type: ' . $type);
    }
}
