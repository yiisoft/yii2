<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\InvalidConfigException;

/**
 * ActiveQuery 表示与 Active Record 类关联的数据库查询。
 *
 * ActiveQuery 可以是普通的查询，也可在关联上下文中使用。
 *
 * ActiveQuery 实例通常由 [[ActiveRecord::find()]] 和 [[ActiveRecord::findBySql()]] 创建。
 * 关联查询由 [[ActiveRecord::hasOne()]] 和 [[ActiveRecord::hasMany()]] 创建。
 *
 * 普通查询
 * ------------
 *
 * ActiveQuery 主要提供以下几种方案去检索查询结果：
 *
 * - [[one()]]：返回第一行数据。
 * - [[all()]]：根据查询结果返回所有的记录。
 * - [[count()]]：返回记录的数量。
 * - [[sum()]]：返回指定列的总和。
 * - [[average()]]：返回指定列的平均值。
 * - [[min()]]：返回指定列的最小值。
 * - [[max()]]：返回指定列的最大值。
 * - [[scalar()]]：返回查询结果第一行中第一列的值。
 * - [[column()]]：返回查询结果中第一列的值。
 * - [[exists()]]：返回一个表明查询结果是否有数据的值。
 *
 * 因为 ActiveQuery 是 [[Query]] 的扩展，所以可以使用查询方法，比如 [[where()]]，
 * [[orderBy()]] 去自定义查询方法。
 *
 * ActiveQuery 还提供以下附加查询选项：
 *
 * - [[with()]]：此查询应执行的关联的列表。
 * - [[joinWith()]]：重用关联查询定义，以便将连接添加到查询中。
 * - [[indexBy()]]：对查询结果进行索引列的名称。
 * - [[asArray()]]：是否将每个记录作为数组返回。
 *
 * 可以使用相同名称的方法配置这些选项。例如：
 *
 * ```php
 * $customers = Customer::find()->with('orders')->asArray()->all();
 * ```
 *
 * 关联查询
 * ----------------
 *
 * 在关联上下文中，ActiveQuery 表示了两个 Active Record 类之间的关系。
 *
 * 关联 ActiveQuery 实例通常由调用 [[ActiveRecord::hasOne()]] 和 [[ActiveRecord::hasMany()]] 来创建。
 * 一个 Active Record 类通过定义一个 getter 方法来声明关联，
 * 该方法通过调用之一并返回创建的 ActiveQuery 对象。
 *
 * 关联由 [[link]] 来指定，表示了不同表的列之间的关联；
 * 并且由 [[multiple]] 来表示关联的多样性。
 *
 * 如果关联涉及到连接表，将会通过 [[via()]] 或 [[viaTable()]] 方法来指定。
 * 这些方法只能在关联上下文中调用。[[inverseOf()]] 也是如此，
 * 将关联标记为另一个关联的逆关联，
 * 并将要添加到关联查询连接条件的条件添加到 [[onCondition()]] 中。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ActiveQuery extends Query implements ActiveQueryInterface
{
    use ActiveQueryTrait;
    use ActiveRelationTrait;

    /**
     * @event Event 通过 [[init()]] 初始化查询时触发的事件。
     */
    const EVENT_INIT = 'init';

    /**
     * @var string 要执行的用于查询 AR 记录的 SQL 语句。
     * 通过 [[ActiveRecord::findBySql()]] 来设置。
     */
    public $sql;
    /**
     * @var string|array 在关联上下文中使用此查询时要用到的连接条件。
     * 当调用 [[ActiveQuery::joinWith()]] 时，将在 ON 部分中使用该条件。
     * 否则，条件将在查询的 WHERE 条件中使用。
     * 有关如何指定此参数，请参照 [[Query::where()]]。
     * @see onCondition()
     */
    public $on;
    /**
     * @var array 此查询应与之关联的列表
     */
    public $joinWith;


    /**
     * 构造函数。
     * @param string $modelClass 与查询关联的模型类
     * @param array $config 要应用于新创建的查询对象的配置
     */
    public function __construct($modelClass, $config = [])
    {
        $this->modelClass = $modelClass;
        parent::__construct($config);
    }

    /**
     * 初始化对象。
     * 在构造函数的末尾调用此方法。默认执行将触发 [[EVENT_INIT]] 事件。
     * 如果你重写这个方法，
     * 确保你在最后要调用父类的执行方法去触发这个事件。
     */
    public function init()
    {
        parent::init();
        $this->trigger(self::EVENT_INIT);
    }

    /**
     * 执行查询并将所有结果作为数组返回。
     * @param Connection $db 用于创建 DB 命令的 DB 连接。
     * 如果为 NULL，将使用 [[modelClass]] 返回的 DB 连接。
     * @return array|ActiveRecord[] 查询的结果。如果没有查询结果，将返回一个空数组。
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($builder)
    {
        // NOTE: 因为可以使用相同的 ActiveQuery 来构建不同的 SQL 语句
        // (e.g. 通过 ActiveDataProvider，一个用于计数查询，另一个用于查询数据的行，
        // 确认使用相同的 ActiveQuery 多次构建 SQL 语句
        // 非常重要。
        if (!empty($this->joinWith)) {
            $this->buildJoinWith();
            $this->joinWith = null;    // 清理它以避免问题 https://github.com/yiisoft/yii2/issues/2687
        }

        if (empty($this->from)) {
            $this->from = [$this->getPrimaryTableName()];
        }

        if (empty($this->select) && !empty($this->join)) {
            list(, $alias) = $this->getTableNameAndAlias();
            $this->select = ["$alias.*"];
        }

        if ($this->primaryModel === null) {
            // 即时加载
            $query = Query::create($this);
        } else {
            // 关联的惰性加载
            $where = $this->where;

            if ($this->via instanceof self) {
                // 通过连接表
                $viaModels = $this->via->findJunctionRows([$this->primaryModel]);
                $this->filterByModels($viaModels);
            } elseif (is_array($this->via)) {
                // 通过关联
                /* @var $viaQuery ActiveQuery */
                list($viaName, $viaQuery) = $this->via;
                if ($viaQuery->multiple) {
                    if ($this->primaryModel->isRelationPopulated($viaName)) {
                        $viaModels = $this->primaryModel->$viaName;
                    } else {
                        $viaModels = $viaQuery->all();
                        $this->primaryModel->populateRelation($viaName, $viaModels);
                    }
                } else {
                    if ($this->primaryModel->isRelationPopulated($viaName)) {
                        $model = $this->primaryModel->$viaName;
                    } else {
                        $model = $viaQuery->one();
                        $this->primaryModel->populateRelation($viaName, $model);
                    }
                    $viaModels = $model === null ? [] : [$model];
                }
                $this->filterByModels($viaModels);
            } else {
                $this->filterByModels([$this->primaryModel]);
            }

            $query = Query::create($this);
            $this->where = $where;
        }

        if (!empty($this->on)) {
            $query->andWhere($this->on);
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function populate($rows)
    {
        if (empty($rows)) {
            return [];
        }

        $models = $this->createModels($rows);
        if (!empty($this->join) && $this->indexBy === null) {
            $models = $this->removeDuplicatedModels($models);
        }
        if (!empty($this->with)) {
            $this->findWith($this->with, $models);
        }

        if ($this->inverseOf !== null) {
            $this->addInverseRelations($models);
        }

        if (!$this->asArray) {
            foreach ($models as $model) {
                $model->afterFind();
            }
        }

        return parent::populate($models);
    }

    /**
     * 通过检查其主键值来删除重复的模型。
     * 这个放在主要在执行链接查询时调用，这可能导致返回重复的行。
     * @param array $models 需要检查的模型
     * @throws InvalidConfigException 如果模型主键为空抛出的异常
     * @return array 唯一的模型
     */
    private function removeDuplicatedModels($models)
    {
        $hash = [];
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pks = $class::primaryKey();

        if (count($pks) > 1) {
            // 复合主键
            foreach ($models as $i => $model) {
                $key = [];
                foreach ($pks as $pk) {
                    if (!isset($model[$pk])) {
                        // 如果主键不是结果集的一部分，则不继续
                        break 2;
                    }
                    $key[] = $model[$pk];
                }
                $key = serialize($key);
                if (isset($hash[$key])) {
                    unset($models[$i]);
                } else {
                    $hash[$key] = true;
                }
            }
        } elseif (empty($pks)) {
            throw new InvalidConfigException("Primary key of '{$class}' can not be empty.");
        } else {
            // 单列主键
            $pk = reset($pks);
            foreach ($models as $i => $model) {
                if (!isset($model[$pk])) {
                    // 如果主键不是结果集的一部分，则不继续
                    break;
                }
                $key = $model[$pk];
                if (isset($hash[$key])) {
                    unset($models[$i]);
                } elseif ($key !== null) {
                    $hash[$key] = true;
                }
            }
        }

        return array_values($models);
    }

    /**
     * 执行查询并返回单行结果。
     * @param Connection|null $db 用于创建 DB 命令的 DB 连接。
     * 如果为 `null`，将使用 [[modelClass]] 返回的 DB 连接。
     * @return ActiveRecord|array|null 单行查询结果。
     * 根据 [[asArray]] 的设置，查询的结果可以是数组或 ActiveRecord 对象。
     * 如果没有查询结果将返回 `null`。
     */
    public function one($db = null)
    {
        $row = parent::one($db);
        if ($row !== false) {
            $models = $this->populate([$row]);
            return reset($models) ?: null;
        }

        return null;
    }

    /**
     * 创建可以用于执行此查询的 DB 命令。
     * @param Connection|null $db 用于创建 DB 命令的 DB 连接。
     * 如果为 `null`，将使用 [[modelClass]] 返回 DB 连接。
     * @return Command 创建的 DB 命令实例。
     */
    public function createCommand($db = null)
    {
        /* @var $modelClass ActiveRecord */
        $modelClass = $this->modelClass;
        if ($db === null) {
            $db = $modelClass::getDb();
        }

        if ($this->sql === null) {
            list($sql, $params) = $db->getQueryBuilder()->build($this);
        } else {
            $sql = $this->sql;
            $params = $this->params;
        }

        $command = $db->createCommand($sql, $params);
        $this->setCommandCache($command);

        return $command;
    }

    /**
     * {@inheritdoc}
     */
    protected function queryScalar($selectExpression, $db)
    {
        /* @var $modelClass ActiveRecord */
        $modelClass = $this->modelClass;
        if ($db === null) {
            $db = $modelClass::getDb();
        }

        if ($this->sql === null) {
            return parent::queryScalar($selectExpression, $db);
        }

        $command = (new Query())->select([$selectExpression])
            ->from(['c' => "({$this->sql})"])
            ->params($this->params)
            ->createCommand($db);
        $this->setCommandCache($command);

        return $command->queryScalar();
    }

    /**
     * 与指定关联的连接。
     *
     * 此方法将允许你重用现有的关联定义来执行 JOIN 查询。
     * 基于指定关联的定义，
     * 该方法将一个或多个 JOIN 语句附加到当前查询。
     *
     * 如果 `$eagerLoading` 参数为真，该方法还将对指定的关联执行即时加载，
     * 相当于使用指定关联调用 [[with()]]。
     *
     * 注意，因为将执行 JOIN 查询，所以你需要消除列名的歧义。
     *
     * 此方法与 [[with()]] 不同之处在于，它将构建并执行主表的 JOIN SQL 语句。
     * 并且当 `$eagerLoading` 为真时，除了指定关联之外，还将调用 [[with()]]。
     *
     * @param string|array $with 要加入的关联。
     * 这可以是表示关联名称的字符串，也可以是具有以下语义的数组：
     *
     * - 每个数组元素表示单个关联。
     * - 你可以将关联名称指定为数组的键，并提供匿名函数，
     *   该函数可以用于动态修改关联查询作为数组值。
     * - 如果关联查询不需要修改，你可以使用关联名作为数组值。
     *
     * 关联名称可以可选地包含关联表的别名（e.g. `books b`）。
     *
     * 也可以指定子关联，请参阅 [[with()]] 的语法。
     *
     * 以下是一些例子：
     *
     * ```php
     * // 查找所有包含书籍的订单，并即使加载 "books"
     * Order::find()->joinWith('books', true, 'INNER JOIN')->all();
     * // 查找所有订单，即时加载 "books"，并按书名对订单和书籍进行排序。
     * Order::find()->joinWith([
     *     'books' => function (\yii\db\ActiveQuery $query) {
     *         $query->orderBy('item.name');
     *     }
     * ])->all();
     * // 查找所有包含 'Science fiction' 类图书的所有订单，图书表将使用 "b" 作为别名
     * Order::find()->joinWith(['books b'], true, 'INNER JOIN')->where(['b.category' => 'Science fiction'])->all();
     * ```
     *
     * 别名语法从 2.0.7 版本开始可用。
     *
     * @param bool|array $eagerLoading 是否即时加载关联
     * 在 `$with`中指定。当为布尔值时，它适用于 `$with` 中指定所有关联。
     * 使用数组明确列出需要即时加载 `$with` 中的哪些关联。
     * 注意，这并不意味着，
     * 从查询结果中填充关联。
     * 仍将执行额外查询以引入相关数据。
     * 默认为 `true`。
     * @param string|array $joinType `$with` 中指定的关联的连接类型。
     * 当它为字符串，它适用于 `$with` 中指定的所有关联。
     * 使用 `relationName => joinType` 格式数组为不同的关联指定不同的连接类型。
     * @return $this 查询对象本身
     */
    public function joinWith($with, $eagerLoading = true, $joinType = 'LEFT JOIN')
    {
        $relations = [];
        foreach ((array) $with as $name => $callback) {
            if (is_int($name)) {
                $name = $callback;
                $callback = null;
            }

            if (preg_match('/^(.*?)(?:\s+AS\s+|\s+)(\w+)$/i', $name, $matches)) {
                // 使用别名定义 relation，调整回调函数以使用别名
                list(, $relation, $alias) = $matches;
                $name = $relation;
                $callback = function ($query) use ($callback, $alias) {
                    /* @var $query ActiveQuery */
                    $query->alias($alias);
                    if ($callback !== null) {
                        call_user_func($callback, $query);
                    }
                };
            }

            if ($callback === null) {
                $relations[] = $name;
            } else {
                $relations[$name] = $callback;
            }
        }
        $this->joinWith[] = [$relations, $eagerLoading, $joinType];
        return $this;
    }

    private function buildJoinWith()
    {
        $join = $this->join;
        $this->join = [];

        /* @var $modelClass ActiveRecordInterface */
        $modelClass = $this->modelClass;
        $model = $modelClass::instance();
        foreach ($this->joinWith as $config) {
            list($with, $eagerLoading, $joinType) = $config;
            $this->joinWithRelations($model, $with, $joinType);

            if (is_array($eagerLoading)) {
                foreach ($with as $name => $callback) {
                    if (is_int($name)) {
                        if (!in_array($callback, $eagerLoading, true)) {
                            unset($with[$name]);
                        }
                    } elseif (!in_array($name, $eagerLoading, true)) {
                        unset($with[$name]);
                    }
                }
            } elseif (!$eagerLoading) {
                $with = [];
            }

            $this->with($with);
        }

        // 移除可能通过 joinWithRelations 添加的重复连接
        // e.g. 当同时加入连接一个关联和通过一个关联时
        $uniqueJoins = [];
        foreach ($this->join as $j) {
            $uniqueJoins[serialize($j)] = $j;
        }
        $this->join = array_values($uniqueJoins);

        if (!empty($join)) {
            // 将显示连接添加到 joinWith()
            // https://github.com/yiisoft/yii2/issues/2880
            $this->join = empty($this->join) ? $join : array_merge($this->join, $join);
        }
    }

    /**
     * 与指定关联的内连接。
     * 这是 [[joinWith()]] 的便捷方法，连接类型设置为 "INNER JOIN"。
     * 关于此方法的详细用法，请参阅 [[joinWith()]]。
     * @param string|array $with 连接的关联
     * @param bool|array $eagerLoading 是否即时加载这个关联。
     * 请注意，
     * 这并不意味着，关联是从查询结果中填充的。
     * 仍将执行额外查询以引入相关数据。
     * @return $this 查询对象本身
     * @see joinWith()
     */
    public function innerJoinWith($with, $eagerLoading = true)
    {
        return $this->joinWith($with, $eagerLoading, 'INNER JOIN');
    }

    /**
     * 通过基于给定的关联添加连接片段来修改当前查询。
     * @param ActiveRecord $model 主模型
     * @param array $with 连接的关联
     * @param string|array $joinType 连接类型
     */
    private function joinWithRelations($model, $with, $joinType)
    {
        $relations = [];

        foreach ($with as $name => $callback) {
            if (is_int($name)) {
                $name = $callback;
                $callback = null;
            }

            $primaryModel = $model;
            $parent = $this;
            $prefix = '';
            while (($pos = strpos($name, '.')) !== false) {
                $childName = substr($name, $pos + 1);
                $name = substr($name, 0, $pos);
                $fullName = $prefix === '' ? $name : "$prefix.$name";
                if (!isset($relations[$fullName])) {
                    $relations[$fullName] = $relation = $primaryModel->getRelation($name);
                    $this->joinWithRelation($parent, $relation, $this->getJoinType($joinType, $fullName));
                } else {
                    $relation = $relations[$fullName];
                }
                /* @var $relationModelClass ActiveRecordInterface */
                $relationModelClass = $relation->modelClass;
                $primaryModel = $relationModelClass::instance();
                $parent = $relation;
                $prefix = $fullName;
                $name = $childName;
            }

            $fullName = $prefix === '' ? $name : "$prefix.$name";
            if (!isset($relations[$fullName])) {
                $relations[$fullName] = $relation = $primaryModel->getRelation($name);
                if ($callback !== null) {
                    call_user_func($callback, $relation);
                }
                if (!empty($relation->joinWith)) {
                    $relation->buildJoinWith();
                }
                $this->joinWithRelation($parent, $relation, $this->getJoinType($joinType, $fullName));
            }
        }
    }

    /**
     * 根据给定的连接类型参数和关联名称返回连接类型。
     * @param string|array $joinType 给定的连接类型
     * @param string $name 关联名称
     * @return string 真正的连接类型
     */
    private function getJoinType($joinType, $name)
    {
        if (is_array($joinType) && isset($joinType[$name])) {
            return $joinType[$name];
        }

        return is_string($joinType) ? $joinType : 'INNER JOIN';
    }

    /**
     * 返回 [[modelClass]] 的表名或是表别名。
     * @return array 表名和表别名。
     * @since 2.0.16
     */
    protected function getTableNameAndAlias()
    {
        if (empty($this->from)) {
            $tableName = $this->getPrimaryTableName();
        } else {
            $tableName = '';
            // 如果 "from" 的第一个值为 alias-tablename-pair，则直接返回
            foreach ($this->from as $alias => $tableName) {
                if (is_string($alias)) {
                    return [$tableName, $alias];
                }
                break;
            }
        }

        if (preg_match('/^(.*?)\s+({{\w+}}|\w+)$/', $tableName, $matches)) {
            $alias = $matches[2];
        } else {
            $alias = $tableName;
        }

        return [$tableName, $alias];
    }

    /**
     * 使用子查询连接父查询。
     * 将相应的修改当前查询对象。
     * @param ActiveQuery $parent
     * @param ActiveQuery $child
     * @param string $joinType
     */
    private function joinWithRelation($parent, $child, $joinType)
    {
        $via = $child->via;
        $child->via = null;
        if ($via instanceof self) {
            // via table
            $this->joinWithRelation($parent, $via, $joinType);
            $this->joinWithRelation($via, $child, $joinType);
            return;
        } elseif (is_array($via)) {
            // via relation
            $this->joinWithRelation($parent, $via[1], $joinType);
            $this->joinWithRelation($via[1], $child, $joinType);
            return;
        }

        list($parentTable, $parentAlias) = $parent->getTableNameAndAlias();
        list($childTable, $childAlias) = $child->getTableNameAndAlias();

        if (!empty($child->link)) {
            if (strpos($parentAlias, '{{') === false) {
                $parentAlias = '{{' . $parentAlias . '}}';
            }
            if (strpos($childAlias, '{{') === false) {
                $childAlias = '{{' . $childAlias . '}}';
            }

            $on = [];
            foreach ($child->link as $childColumn => $parentColumn) {
                $on[] = "$parentAlias.[[$parentColumn]] = $childAlias.[[$childColumn]]";
            }
            $on = implode(' AND ', $on);
            if (!empty($child->on)) {
                $on = ['and', $on, $child->on];
            }
        } else {
            $on = $child->on;
        }
        $this->join($joinType, empty($child->from) ? $childTable : $child->from, $on);

        if (!empty($child->where)) {
            $this->andWhere($child->where);
        }
        if (!empty($child->having)) {
            $this->andHaving($child->having);
        }
        if (!empty($child->orderBy)) {
            $this->addOrderBy($child->orderBy);
        }
        if (!empty($child->groupBy)) {
            $this->addGroupBy($child->groupBy);
        }
        if (!empty($child->params)) {
            $this->addParams($child->params);
        }
        if (!empty($child->join)) {
            foreach ($child->join as $join) {
                $this->join[] = $join;
            }
        }
        if (!empty($child->union)) {
            foreach ($child->union as $union) {
                $this->union[] = $union;
            }
        }
    }

    /**
     * 设置关联查询的 ON 条件。
     * 调用 [[ActiveQuery::joinWith()]] 时，将在 ON 部分中使用该条件。
     * 否则，条件将在查询的 WHERE 部分中使用。
     *
     * 在 [[ActiveRecord]] 类声明关联时，使用此方法指定额外条件：
     *
     * ```php
     * public function getActiveUsers()
     * {
     *     return $this->hasMany(User::className(), ['id' => 'user_id'])
     *                 ->onCondition(['active' => true]);
     * }
     * ```
     *
     * 请注意，此条件适用于连接以及获取相关记录时。
     * 因此，在该条件中只能使用相关表的字段。尝试访问主记录的字段将导致
     * non-join-query 的错误。
     *
     * @param string|array $condition ON 条件。请参阅 [[Query::where()]] 查看如果指定参数。
     * @param array $params 要绑定到查询的参数（name => value）。
     * @return $this 查询对象本身
     */
    public function onCondition($condition, $params = [])
    {
        $this->on = $condition;
        $this->addParams($params);
        return $this;
    }

    /**
     * 向现有条件添加额外的 ON 条件。
     * 新条件和现有条件将使用 'AND' 运算符链接。
     * @param string|array $condition 新的 ON 条件，
     * 请参阅 [[where()]] 关于如何指定参数。
     * @param array $params 要绑定到查询的参数（name => value）。
     * @return $this 查询对象本身
     * @see onCondition()
     * @see orOnCondition()
     */
    public function andOnCondition($condition, $params = [])
    {
        if ($this->on === null) {
            $this->on = $condition;
        } else {
            $this->on = ['and', $this->on, $condition];
        }
        $this->addParams($params);
        return $this;
    }

    /**
     * 将附加条件 ON 添加到现有条件中。
     * 新条件和现有的条件都将使用 'OR' 运算符连接。
     * @param string|array $condition 新的 ON 条件。有关如何指定此参数，
     * 请参阅 [[where()]]。
     * @param array $params 参数 (name => value) 将被绑定到查询条件中。
     * @return $this 查询对象本身
     * @see onCondition()
     * @see andOnCondition()
     */
    public function orOnCondition($condition, $params = [])
    {
        if ($this->on === null) {
            $this->on = $condition;
        } else {
            $this->on = ['or', $this->on, $condition];
        }
        $this->addParams($params);
        return $this;
    }

    /**
     * 指定关联查询的连接表。
     *
     * 在 [[ActiveRecord]] 类中声明关联时，使用此方法去指定连接表：
     *
     * ```php
     * public function getItems()
     * {
     *     return $this->hasMany(Item::className(), ['id' => 'item_id'])
     *                 ->viaTable('order_item', ['order_id' => 'id']);
     * }
     * ```
     *
     * @param string $tableName 连接表的名称。
     * @param array $link 连接表和关联表 [[primaryModel]] 之间的连接。
     * 数组的键表示连接表的列，
     * 值表示 [[primaryModel]] 表中的列。
     * @param callable $callable 一个 PHP 的回调，用于定制与连接表相关的关联。
     * 它的签名是 `function($query)`，其中 `$query` 是要定制的查询。
     * @return $this 查询对象本身
     * @see via()
     */
    public function viaTable($tableName, $link, callable $callable = null)
    {
        $modelClass = $this->primaryModel !== null ? get_class($this->primaryModel) : __CLASS__;

        $relation = new self($modelClass, [
            'from' => [$tableName],
            'link' => $link,
            'multiple' => true,
            'asArray' => true,
        ]);
        $this->via = $relation;
        if ($callable !== null) {
            call_user_func($callable, $relation);
        }

        return $this;
    }

    /**
     * 为 [[modelClass]] 中的表定义别名。
     *
     * 此方法将调整 [[from]] 以便覆盖已定义的别名。
     * 如果没有定义，[[from]] 将会使用给定的别名。
     *
     * @param string $alias 表的别名.
     * @return $this 查询对象本身
     * @since 2.0.7
     */
    public function alias($alias)
    {
        if (empty($this->from) || count($this->from) < 2) {
            list($tableName) = $this->getTableNameAndAlias();
            $this->from = [$alias => $tableName];
        } else {
            $tableName = $this->getPrimaryTableName();

            foreach ($this->from as $key => $table) {
                if ($table === $tableName) {
                    unset($this->from[$key]);
                    $this->from[$alias] = $tableName;
                }
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.12
     */
    public function getTablesUsedInFrom()
    {
        if (empty($this->from)) {
            return $this->cleanUpTableNames([$this->getPrimaryTableName()]);
        }

        return parent::getTablesUsedInFrom();
    }

    /**
     * @return string 主表名
     * @since 2.0.12
     */
    protected function getPrimaryTableName()
    {
        /* @var $modelClass ActiveRecord */
        $modelClass = $this->modelClass;
        return $modelClass::tableName();
    }
}
