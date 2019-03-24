<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\data;

use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveQueryInterface;
use yii\db\Connection;
use yii\db\QueryInterface;
use yii\di\Instance;

/**
 * ActiveDataProvider 基于 [[\yii\db\Query]] 和 [[\yii\db\ActiveQuery]] 实现了数据提供器。
 *
 * ActiveDataProvider 通过使用 [[query]] 执行数据库查询来提供数据。
 *
 * 以下是使用 ActiveDataProvider 提供 ActiveRecord 实例的示例：
 *
 * ```php
 * $provider = new ActiveDataProvider([
 *     'query' => Post::find(),
 *     'pagination' => [
 *         'pageSize' => 20,
 *     ],
 * ]);
 *
 * // 获取当前页的 posts
 * $posts = $provider->getModels();
 * ```
 *
 * 下面的示例演示如何使用不带 ActiveRecord 的 ActiveDataProvider：
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
 * // 获取当前页的 posts
 * $posts = $provider->getModels();
 * ```
 *
 * 有关 ActiveDataProvider 的详细信息和使用信息，请参阅 [guide article on data providers](guide:output-data-providers)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveDataProvider extends BaseDataProvider
{
    /**
     * @var QueryInterface 用于获取数据模型和 [[totalCount]]
     * 如果未显示设置。
     */
    public $query;
    /**
     * @var string|callable 用作数据模型键的列。
     * 可以是列名，也可以是返回给定数据模型的键值的回调函数。
     *
     * 如果未设置，将使用以下规则确定数据模型的键：
     *
     * - 如果 [[query]] 是一个 [[\yii\db\ActiveQuery]] 实例，则将使用 [[\yii\db\ActiveQuery::modelClass]] 的主键。
     * - 否则，将使用 [[models]] 数组的键。
     *
     * @see getKeys()
     */
    public $key;
    /**
     * @var Connection|array|string 数据库连接对象或数据库连接的应用程序组件 ID。
     * 如果未设置，将使用默认的 DB 连接。
     * 从 2.0.2 版开始，它也可以是用于创建对象的配置数组。
     */
    public $db;


    /**
     * 初始化数据库连接组件
     * 此方法将初始化 [[db]] 属性，以确保它引用有效的 db 连接。
     * @throws InvalidConfigException 如果 [[db]] 不可用。
     */
    public function init()
    {
        parent::init();
        if (is_string($this->db)) {
            $this->db = Instance::ensure($this->db, Connection::className());
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
            /* @var $class \yii\db\ActiveRecordInterface */
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
        if (($sort = $this->getSort()) !== false && $this->query instanceof ActiveQueryInterface) {
            /* @var $modelClass Model */
            $modelClass = $this->query->modelClass;
            $model = $modelClass::instance();
            if (empty($sort->attributes)) {
                foreach ($model->attributes() as $attribute) {
                    $sort->attributes[$attribute] = [
                        'asc' => [$attribute => SORT_ASC],
                        'desc' => [$attribute => SORT_DESC],
                        'label' => $model->getAttributeLabel($attribute),
                    ];
                }
            } else {
                foreach ($sort->attributes as $attribute => $config) {
                    if (!isset($config['label'])) {
                        $sort->attributes[$attribute]['label'] = $model->getAttributeLabel($attribute);
                    }
                }
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
