<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\data;

use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveQueryInterface;
use yii\db\Connection;
use yii\db\QueryInterface;
use yii\di\Instance;

/**
 * ActiveDataProvider implements a data provider based on [[\yii\db\Query]] and [[\yii\db\ActiveQuery]].
 *
 * ActiveDataProvider provides data by performing DB queries using [[query]].
 *
 * The following is an example of using ActiveDataProvider to provide ActiveRecord instances:
 *
 * ```php
 * $provider = new ActiveDataProvider([
 *     'query' => Post::find(),
 *     'pagination' => [
 *         'pageSize' => 20,
 *     ],
 * ]);
 *
 * // get the posts in the current page
 * $posts = $provider->getModels();
 * ```
 *
 * And the following example shows how to use ActiveDataProvider without ActiveRecord:
 *
 * ```php
 * $query = new Query();
 * $provider = new ActiveDataProvider([
 *     'query' => $query->from('post'),
 *     'pagination' => [
 *         'pageSize' => 20,
 *     ],
 * ]);
 *
 * // get the posts in the current page
 * $posts = $provider->getModels();
 * ```
 *
 * For more details and usage information on ActiveDataProvider, see the [guide article on data providers](guide:output-data-providers).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveDataProvider extends BaseDataProvider
{
    /**
     * @var QueryInterface|null the query that is used to fetch data models and [[totalCount]] if it is not explicitly set.
     */
    public $query;
    /**
     * @var string|callable|null the column that is used as the key of the data models.
     * This can be either a column name, or a callable that returns the key value of a given data model.
     *
     * If this is not set, the following rules will be used to determine the keys of the data models:
     *
     * - If [[query]] is an [[\yii\db\ActiveQuery]] instance, the primary keys of [[\yii\db\ActiveQuery::modelClass]] will be used.
     * - Otherwise, the keys of the [[models]] array will be used.
     *
     * @see getKeys()
     */
    public $key;
    /**
     * @var Connection|array|string|null the DB connection object or the application component ID of the DB connection.
     * If set it overrides [[query]] default DB connection.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $db;


    /**
     * Initializes the DB connection component.
     * This method will initialize the [[db]] property (when set) to make sure it refers to a valid DB connection.
     * @throws InvalidConfigException if [[db]] is invalid.
     */
    public function init()
    {
        parent::init();
        if ($this->db !== null) {
            $this->db = Instance::ensure($this->db);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareModels()
    {
        if (!$this->query instanceof QueryInterface) {
            throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }
        $query = clone $this->query;
        if (($pagination = $this->getPagination()) !== false) {
            $pagination->totalCount = $this->getTotalCount();
            if ($pagination->totalCount === 0) {
                return [];
            }
            $query->limit($pagination->getLimit())->offset($pagination->getOffset());
        }
        if (($sort = $this->getSort()) !== false) {
            $query->addOrderBy($sort->getOrders());
        }

        return $query->all($this->db);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareKeys($models)
    {
        $keys = [];
        if ($this->key !== null) {
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }

            return $keys;
        } elseif ($this->query instanceof ActiveQueryInterface) {
            /** @var \yii\db\ActiveRecordInterface $class */
            $class = $this->query->modelClass;
            $pks = $class::primaryKey();
            if (count($pks) === 1) {
                $pk = $pks[0];
                foreach ($models as $model) {
                    $keys[] = $model[$pk];
                }
            } else {
                foreach ($models as $model) {
                    $kk = [];
                    foreach ($pks as $pk) {
                        $kk[$pk] = $model[$pk];
                    }
                    $keys[] = $kk;
                }
            }

            return $keys;
        }

        return array_keys($models);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareTotalCount()
    {
        if (!$this->query instanceof QueryInterface) {
            throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }
        $query = clone $this->query;
        return (int) $query->limit(-1)->offset(-1)->orderBy([])->count('*', $this->db);
    }

    /**
     * {@inheritdoc}
     */
    public function setSort($value)
    {
        parent::setSort($value);
        if ($this->query instanceof ActiveQueryInterface && ($sort = $this->getSort()) !== false) {
            /** @var Model $modelClass */
            $modelClass = $this->query->modelClass;
            $model = $modelClass::instance();
            if (empty($sort->attributes)) {
                foreach ($model->attributes() as $attribute) {
                    $sort->attributes[$attribute] = [
                        'asc' => [$attribute => SORT_ASC],
                        'desc' => [$attribute => SORT_DESC],
                    ];
                }
            }
            if ($sort->modelClass === null) {
                $sort->modelClass = $modelClass;
            }
        }
    }

    public function __clone()
    {
        if (is_object($this->query)) {
            $this->query = clone $this->query;
        }

        parent::__clone();
    }
}
