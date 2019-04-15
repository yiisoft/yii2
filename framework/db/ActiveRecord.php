<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * ActiveRecord 是表示数据对象关系的类的基类。
 *
 * ActiveRecord 实现请阅读 [Active Record design pattern](http://en.wikipedia.org/wiki/Active_record)。
 * Active Record 背后的前提是，单个 [[ActiveRecord]] 对象与数据库表中的特定行相关联。
 * 对象的属性映射到相应表的列。
 * 引用 Active Record 属性等同于访问该记录的相应表列。
 *
 * 例如，假设 `Customer` ActiveRecord 类与 `customer` 表相关联。
 * 这意味着类的 `name` 属性会自动映射到 `customer` 表中的 `name` 列。
 * 感谢伟大的 Active Record，当变量 `$customer` 是 `Customer` 类的对象时，
 * 为了得到表行的 `name` 列的值，你可以使用表达式 `$customer->name` 获取它。
 * 在此示例中，ActiveRecord 提供了一个面向对象的接口，用于访问存储在数据库中的数据。
 * 但 Active Record 提供了比这更多的功能。
 *
 * 要声明一个 ActiveRecord 类，
 * 你需要继承 [[\yii\db\ActiveRecord]] 并实现 `tableName` 方法：
 *
 * ```php
 * <?php
 *
 * class Customer extends \yii\db\ActiveRecord
 * {
 *     public static function tableName()
 *     {
 *         return 'customer';
 *     }
 * }
 * ```
 *
 * `tableName` 方法仅会返回与该类关联的数据库表的名称。
 *
 * > 提示：您还可以使用 [Gii code generator](guide:start-gii)
 * > 从数据库表生成 ActiveRecord 类。
 *
 * 类实例可通过以下两种方式的任何一种获得：
 *
 * * 使用 `new` 操作符，创建一个新的空对象
 * * 使用方法从数据库中获取现有记录（或记录）
 *
 * 下面是一个示例，显示 ActiveRecord 的一些典型用法：
 *
 * ```php
 * $user = new User();
 * $user->name = 'Qiang';
 * $user->save();  // a new row is inserted into user table
 *
 * // the following will retrieve the user 'CeBe' from the database
 * $user = User::find()->where(['name' => 'CeBe'])->one();
 *
 * // this will get related records from orders table when relation is defined
 * $orders = $user->orders;
 * ```
 *
 * 有关 ActiveRecord 的更多详细信息和用法，请参阅 [guide article on ActiveRecord](guide:db-active-record)。
 *
 * @method ActiveQuery hasMany($class, array $link) 有关详细信息请参阅 [[BaseActiveRecord::hasMany()]]
 * @method ActiveQuery hasOne($class, array $link) 有关详细信息请参阅 [[BaseActiveRecord::hasOne()]]
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ActiveRecord extends BaseActiveRecord
{
    /**
     * 插入操作。其主要用于覆盖 [[transactions()]] 以指定哪些操作是事务性的。
     */
    const OP_INSERT = 0x01;
    /**
     * 更新操作。其主要用于覆盖 [[transactions()]] 以指定哪些操作是事务性的。
     */
    const OP_UPDATE = 0x02;
    /**
     * 删除操作。其主要用于覆盖 [[transactions()]] 以指定哪些操作是事务性的。
     */
    const OP_DELETE = 0x04;
    /**
     * 所有三个操作：insert、update、delete。
     * 这是表达式的快捷方式：OP_INSERT | OP_UPDATE | OP_DELETE。
     */
    const OP_ALL = 0x07;


    /**
     * 从数据库表结构加载默认值。
     *
     * 你可以在创建新实例后调用此方法以加载默认值：
     *
     * ```php
     * // class Customer extends \yii\db\ActiveRecord
     * $customer = new Customer();
     * $customer->loadDefaultValues();
     * ```
     *
     * @param bool $skipIfSet 是否应保留现有值。
     * 这只会为 `null` 属性设置默认值。
     * @return $this 模型实例本身。
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        foreach (static::getTableSchema()->columns as $column) {
            if ($column->defaultValue !== null && (!$skipIfSet || $this->{$column->name} === null)) {
                $this->{$column->name} = $column->defaultValue;
            }
        }

        return $this;
    }

    /**
     * 返回此 AR 类使用的数据库连接。
     * 默认情况下，"db" 组件用作数据库连接。
     * 如果要使用其他数据库连接，可以重写此方法。
     * @return Connection 此 AR 类使用的数据库连接。
     */
    public static function getDb()
    {
        return Yii::$app->getDb();
    }

    /**
     * 使用给定的 SQL 语句创建 [[ActiveQuery]] 实例。
     *
     * 请注意，因为已经指定了 SQL 语句，
     * 所以在创建的 [[ActiveQuery]] 实例上调用其他查询修改方法（例如 `where()`，`order()`），
     * 将不起作用，
     * 但是，调用 `with()`，`asArray()` 或 `indexBy()` 仍然没问题。
     *
     * 下面举个例子：
     *
     * ```php
     * $customers = Customer::findBySql('SELECT * FROM customer')->all();
     * ```
     *
     * @param string $sql 要执行的 SQL 语句
     * @param array $params 在执行期间绑定到 SQL 语句的参数。
     * @return ActiveQuery 新创建的 [[ActiveQuery]] 实例
     */
    public static function findBySql($sql, $params = [])
    {
        $query = static::find();
        $query->sql = $sql;

        return $query->params($params);
    }

    /**
     * 按给定的条件查找 ActiveRecord 实例。
     * 此方法由 [[findOne()]] 和 [[findAll()]] 在内部调用。
     * @param mixed $condition 有关此参数的说明请参阅 [[findOne()]]
     * @return ActiveQueryInterface 新创建的 [[ActiveQueryInterface|ActiveQuery]] 实例。
     * @throws InvalidConfigException 如果没有定义主键，则抛出异常。
     * @internal
     */
    protected static function findByCondition($condition)
    {
        $query = static::find();

        if (!ArrayHelper::isAssociative($condition)) {
            // query by primary key
            $primaryKey = static::primaryKey();
            if (isset($primaryKey[0])) {
                $pk = $primaryKey[0];
                if (!empty($query->join) || !empty($query->joinWith)) {
                    $pk = static::tableName() . '.' . $pk;
                }
                // if condition is scalar, search for a single primary key, if it is array, search for multiple primary key values
                $condition = [$pk => is_array($condition) ? array_values($condition) : $condition];
            } else {
                throw new InvalidConfigException('"' . get_called_class() . '" must have a primary key.');
            }
        } elseif (is_array($condition)) {
            $condition = static::filterCondition($condition);
        }

        return $query->andWhere($condition);
    }

    /**
     * 在将数组条件分配给查询过滤器之前对其进行过滤。
     *
     * 此方法将确保数组条件仅过滤现有表列。
     *
     * @param array $condition 过滤条件。
     * @return array 过滤后的条件。
     * @throws InvalidArgumentException 当数组中包含不安全的值时，抛出异常。
     * @since 2.0.15
     * @internal
     */
    protected static function filterCondition(array $condition)
    {
        $result = [];
        // valid column names are table column names or column names prefixed with table name
        $columnNames = static::getTableSchema()->getColumnNames();
        $tableName = static::tableName();
        $columnNames = array_merge($columnNames, array_map(function($columnName) use ($tableName) {
            return "$tableName.$columnName";
        }, $columnNames));
        foreach ($condition as $key => $value) {
            if (is_string($key) && !in_array($key, $columnNames, true)) {
                throw new InvalidArgumentException('Key "' . $key . '" is not a column name and can not be used as a filter');
            }
            $result[$key] = is_array($value) ? array_values($value) : $value;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function refresh()
    {
        $query = static::find();
        $tableName = key($query->getTablesUsedInFrom());
        $pk = [];
        // disambiguate column names in case ActiveQuery adds a JOIN
        foreach ($this->getPrimaryKey(true) as $key => $value) {
            $pk[$tableName . '.' . $key] = $value;
        }
        $query->where($pk);

        /* @var $record BaseActiveRecord */
        $record = $query->one();
        return $this->refreshInternal($record);
    }

    /**
     * 使用提供的属性值和条件更新整个表。
     *
     * 比如，要将所有状态为 2 的客户的状态更改为 1：
     *
     * ```php
     * Customer::updateAll(['status' => 1], 'status = 2');
     * ```
     *
     * > 警告：如果未指定任何条件，则此方法将更新表中的**所有**行。
     *
     * 注意，此方法不会触发任何事件，如果需要触发 [[EVENT_BEFORE_UPDATE]] 或 [[EVENT_AFTER_UPDATE]] ，
     * 则首先需要 [[find()|find]] 模型，然后在每个模型上调用 [[update()]]。
     * 例如，下面的例子和前面的例子作用是相同的：
     *
     * ```php
     * $models = Customer::find()->where('status = 2')->all();
     * foreach ($models as $model) {
     *     $model->status = 1;
     *     $model->update(false); // skipping validation as no user input is involved
     * }
     * ```
     *
     * 对于大量模型，可以考虑使用 [[ActiveQuery::each()]] 将内存使用限制在规定范围内。
     *
     * @param array $attributes 要保存在表中的属性值（键值对）
     * @param string|array $condition 将放在 UPDATE SQL 的 WHERE 部分中的条件。
     * 有关如何指定此参数，请阅读 [[Query::where()]]。
     * @param array $params 要绑定到查询的参数 (name => value)。
     * @return int 更新的行数
     */
    public static function updateAll($attributes, $condition = '', $params = [])
    {
        $command = static::getDb()->createCommand();
        $command->update(static::tableName(), $attributes, $condition, $params);

        return $command->execute();
    }

    /**
     * 使用提供的计数器更改条件更新整个表。
     *
     * 例如，要将所有客户的年龄增加 1，
     *
     * ```php
     * Customer::updateAllCounters(['age' => 1]);
     * ```
     *
     * 注意，此方法不会触发任何事件。
     *
     * @param array $counters 要更新的计数器 (attribute name => increment value)。
     * 如果要递减计数器，请使用负值。
     * @param string|array $condition 将放在 UPDATE SQL 的 WHERE 部分中的条件。
     * 有关如何指定此参数，请阅读 [[Query::where()]]。
     * @param array $params 要绑定到查询的参数 (name => value)。
     * 不要将参数命名为 `:bp0`，`:bp1` 等，因为它们是由这个方法内部使用的。
     * @return int 更新的行数
     */
    public static function updateAllCounters($counters, $condition = '', $params = [])
    {
        $n = 0;
        foreach ($counters as $name => $value) {
            $counters[$name] = new Expression("[[$name]]+:bp{$n}", [":bp{$n}" => $value]);
            $n++;
        }
        $command = static::getDb()->createCommand();
        $command->update(static::tableName(), $counters, $condition, $params);

        return $command->execute();
    }

    /**
     * 使用提供的条件删除表中的行。
     *
     * 例如，要删除所有状态为 3 的客户：
     *
     * ```php
     * Customer::deleteAll('status = 3');
     * ```
     *
     * > 警告：如果未指定任何条件，则此方法将删除表中的**所有**行。
     *
     * 注意，此方法不会触发任何事件。如果需要触发 [[EVENT_BEFORE_DELETE]] 或 [[EVENT_AFTER_DELETE]]，
     * 则首先需要 [[find()|find]] 模型，然后再每个模型上调用 [[delete()]]。
     * 例如，下面的例子和前面的例子作用是相同的：
     *
     * ```php
     * $models = Customer::find()->where('status = 3')->all();
     * foreach ($models as $model) {
     *     $model->delete();
     * }
     * ```
     *
     * 对于大量模型，可以考虑使用 [[ActiveQuery::each()]] 将内存使用限制在规定范围内。
     *
     * @param string|array $condition 将放在 DELETE SQL 的 WHERE 部分中的条件。
     * 有关如何指定此参数，请阅读 [[Query::where()]]。
     * @param array $params 要绑定到查询的参数 (name => value)。
     * @return int 删除的行数
     */
    public static function deleteAll($condition = null, $params = [])
    {
        $command = static::getDb()->createCommand();
        $command->delete(static::tableName(), $condition, $params);

        return $command->execute();
    }

    /**
     * {@inheritdoc}
     * @return ActiveQuery 新建的 [[ActiveQuery]] 实例。
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }

    /**
     * 声明与此 AR 类关联的数据库表的名称。
     * 默认情况下，此方法通过使用前缀 [[Connection::tablePrefix]] 调用 [[Inflector::camel2id()]] 来返回类名作为表名。
     * 例如，如果 [[Connection::tablePrefix]] 是 `tbl_`，
     * 则 `Customer` 变为 `tbl_customer`，`OrderItem` 变为 `tbl_order_item`
     * 如果未按约定命名表，则可以重写此方法。
     * @return string 表名
     */
    public static function tableName()
    {
        return '{{%' . Inflector::camel2id(StringHelper::basename(get_called_class()), '_') . '}}';
    }

    /**
     * 返回与此 AR 类关联的 DB 表的结构信息。
     * @return TableSchema 与此 AR 类关联的 DB 表的结构信息。
     * @throws InvalidConfigException 如果 AR 类的表不存在，抛出异常。
     */
    public static function getTableSchema()
    {
        $tableSchema = static::getDb()
            ->getSchema()
            ->getTableSchema(static::tableName());

        if ($tableSchema === null) {
            throw new InvalidConfigException('The table does not exist: ' . static::tableName());
        }

        return $tableSchema;
    }

    /**
     * 返回此 AR 类的主键名称。
     * 默认实现将返回与此 AR 类
     * 关联的 DB 表中声明的主键。
     *
     * 如果 DB 表中为声明任何主键，
     * 则应重写此方法，
     * 以返回要用作此 AR 类的主键属性。
     *
     * 请注意，即使对于具有单个主键的表，也应返回一个数组。
     *
     * @return string[] 相关数据表的主键。
     */
    public static function primaryKey()
    {
        return static::getTableSchema()->primaryKey;
    }

    /**
     * 返回模型的所有的属性名称的列表。
     * 默认实现将返回与此 AR 类关联的表的所有列名。
     * @return array 属性名称列表。
     */
    public function attributes()
    {
        return array_keys(static::getTableSchema()->columns);
    }

    /**
     * 声明应在不同场景的事务中执行那些 DB 操作。
     * 支持的 DB 操作为 [[OP_INSERT]]，[[OP_UPDATE]] 以及 [[OP_DELETE]]，
     * 分别对应 [[insert()]]，[[update()]] 以及 [[delete()]] 方法，
     * 默认情况下，这些方法不包含在数据库事务中。
     *
     * 在某些情况下，为保持数据一致性，
     * 你可能希望将其中的部分或全部包含在事务中。
     * 你可以通过重写此方法并返回需要进行事务处理的操作来完成此操作。例如，
     *
     * ```php
     * return [
     *     'admin' => self::OP_INSERT,
     *     'api' => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
     *     // the above is equivalent to the following:
     *     // 'api' => self::OP_ALL,
     *
     * ];
     * ```
     *
     * 上述声明指定在 "admin" 场景中，
     * 插入操作 ([[insert()]]) 应该在事务中完成；
     * 在 "api" 场景中，所有的操作都应该在事务中完成。
     *
     * @return array 事务操作声明。数组的键是场景名称，
     * 数组值是相应的事务操作。
     */
    public function transactions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function populateRecord($record, $row)
    {
        $columns = static::getTableSchema()->columns;
        foreach ($row as $name => $value) {
            if (isset($columns[$name])) {
                $row[$name] = $columns[$name]->phpTypecast($value);
            }
        }
        parent::populateRecord($record, $row);
    }

    /**
     * 使用此属性的记录值将行插入关联的数据库表中。
     *
     * 此方法按顺序执行以下步骤：
     *
     * 1. 当 `$runValidation` 为 `true` 时调用 [[beforeValidate()]]。
     *    如果 [[beforeValidate()]] 返回 `false`，则跳过其余步骤；
     * 2. 当 `$runValidation` 为 `true` 时调用 [[afterValidate()]]。
     *    如果验证失败，则跳过其余步骤；
     * 3. 调用 [[beforeSave()]]。当 [[beforeSave()]] 返回 `false`，
     *    则跳过其余步骤；
     * 4. 插入记录到数据库。如果插入记录失败，则跳过其余步骤；
     * 5. 调用 [[afterSave()]]；
     *
     * 在上面的步骤 1，2，3 和 5 中，
     * 将通过相应的方法引发事件 [[EVENT_BEFORE_VALIDATE]]，
     * [[EVENT_AFTER_VALIDATE]]，[[EVENT_BEFORE_INSERT]]，以及 [[EVENT_AFTER_INSERT]]。
     *
     * 只有 [[dirtyAttributes|changed attribute values]] 才会插入到数据库中。
     *
     * 如果表的主键是自动增量并且在插入期间为 `null`，
     * 则插入后将填充实际值。
     *
     * 例如，要插入 customer 记录：
     *
     * ```php
     * $customer = new Customer;
     * $customer->name = $name;
     * $customer->email = $email;
     * $customer->insert();
     * ```
     *
     * @param bool $runValidation 是否在保存记录之前执行验证（调用 [[validate()]]）。
     * 默认为 `true`，即执行验证。如果验证失败，则记录不会保存到数据库中，
     * 并且此方法将返回 `false`。
     * @param array $attributes 需要保存的属性列表。
     * 默认为 `null`，表示将保存从 DB 加载的所有属性。
     * @return bool 属性是否有效，以及是否成功插入记录。
     * @throws \Exception|\Throwable 如果插入失败，则抛出异常。
     */
    public function insert($runValidation = true, $attributes = null)
    {
        if ($runValidation && !$this->validate($attributes)) {
            Yii::info('Model not inserted due to validation error.', __METHOD__);
            return false;
        }

        if (!$this->isTransactional(self::OP_INSERT)) {
            return $this->insertInternal($attributes);
        }

        $transaction = static::getDb()->beginTransaction();
        try {
            $result = $this->insertInternal($attributes);
            if ($result === false) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }

            return $result;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * 在不考虑事务的情况下将 ActiveRecord 插入到 DB 中。
     * @param array $attributes 需要保存的属性列表。
     * 默认为 `null`，表示将保存从 DB 加载的所有属性。
     * @return bool 是否成功插入记录。
     */
    protected function insertInternal($attributes = null)
    {
        if (!$this->beforeSave(true)) {
            return false;
        }
        $values = $this->getDirtyAttributes($attributes);
        if (($primaryKeys = static::getDb()->schema->insert(static::tableName(), $values)) === false) {
            return false;
        }
        foreach ($primaryKeys as $name => $value) {
            $id = static::getTableSchema()->columns[$name]->phpTypecast($value);
            $this->setAttribute($name, $id);
            $values[$name] = $id;
        }

        $changedAttributes = array_fill_keys(array_keys($values), null);
        $this->setOldAttributes($values);
        $this->afterSave(true, $changedAttributes);

        return true;
    }

    /**
     * 将对此活动记录的更改保存到关联的数据库表中。
     *
     * 此方法将按顺序执行一下步骤：
     *
     * 1. 当 `$runValidation` 为 `true` 时，调用 [[beforeValidate()]]。
     *    如果 [[beforeValidate()]] 返回 `false`，则跳过其余步骤；
     * 2. 当 `$runValidation` 为 `true` 时，调用 [[afterValidate()]]。
     *    如果验证失败，则跳过其余步骤；
     * 3. 调用 [[beforeSave()]]。如果 [[beforeSave()]] 返回 `false`，
     *    则跳过其余步骤；
     * 4. 保存记录到数据中。如果保存失败，则跳过余下步骤；
     * 5. 调用 [[afterSave()]]；
     *
     * 在上面的步骤1，2，3，以及 5 中，
     * 将通过相应的方法引发事件 [[EVENT_BEFORE_VALIDATE]]，
     * [[EVENT_AFTER_VALIDATE]]，[[EVENT_BEFORE_UPDATE]]，以及 [[EVENT_AFTER_UPDATE]]。
     *
     * 只有 [[dirtyAttributes|changed attribute values]] 才会保存到数据库。
     *
     * 例如，要更新 customer 记录：
     *
     * ```php
     * $customer = Customer::findOne($id);
     * $customer->name = $name;
     * $customer->email = $email;
     * $customer->update();
     * ```
     *
     * 注意，更新可能不会影响表中的任何行。
     * 在该情况下，此方法有可能返回 0。
     * 因此，应使用以下代码检查 update() 是否成功：
     *
     * ```php
     * if ($customer->update() !== false) {
     *     // update successful
     * } else {
     *     // update failed
     * }
     * ```
     *
     * @param bool $runValidation 是否在保存记录之前执行验证 （调用 [[validate()]]）。
     * 默认为 `true`，即执行验证。如果验证失败，则记录不会保存到数据库中，
     * 并且此方法将返回 `false`。
     * @param array $attributeNames 需要保存的属性列表。
     * 默认为 `null`，表示将保存从 DB 加载的所有属性。
     * @return int|false 受影响的行数，如果验证失败或 [[beforeSave()]] 停止更新过程，
     * 则为 false。
     * @throws StaleObjectException 如果启用了 [[optimisticLock|optimistic locking]]
     * 并且正在更新的数据已过时，则抛出异常。
     * @throws \Exception|\Throwable 万一更新失败，则抛出异常。
     */
    public function update($runValidation = true, $attributeNames = null)
    {
        if ($runValidation && !$this->validate($attributeNames)) {
            Yii::info('Model not updated due to validation error.', __METHOD__);
            return false;
        }

        if (!$this->isTransactional(self::OP_UPDATE)) {
            return $this->updateInternal($attributeNames);
        }

        $transaction = static::getDb()->beginTransaction();
        try {
            $result = $this->updateInternal($attributeNames);
            if ($result === false) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }

            return $result;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * 删除与此活动记录对应的表行。
     *
     * 此方法将按顺序执行以下步骤：
     *
     * 1. 调用 [[beforeDelete()]]。
     *    如果该方法返回 `false`，则跳过其余步骤；
     * 2. 从数据库删除记录；
     * 3. 调用 [[afterDelete()]]。
     *
     * 在以上步骤 1 和 3 中，
     * 将通过相应的方法引发事件 [[EVENT_BEFORE_DELETE]] 和 [[EVENT_AFTER_DELETE]]。
     *
     * @return int|false 删除的行数，如果由于某种原因删除失败，则为 `false`。
     * 注意，即使删除执行成功，删除的行数也可能为 0。
     * @throws StaleObjectException 如果启用了 [[optimisticLock|optimistic locking]]
     * 并且当正在删除的数据已过时，则抛出异常。
     * @throws \Exception|\Throwable 万一删除失败，则抛出异常。
     */
    public function delete()
    {
        if (!$this->isTransactional(self::OP_DELETE)) {
            return $this->deleteInternal();
        }

        $transaction = static::getDb()->beginTransaction();
        try {
            $result = $this->deleteInternal();
            if ($result === false) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }

            return $result;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * 删除 ActiveRecord 而不考虑事务。
     * @return int|false 删除的行数，如果由于某种原因删除失败，则为 `false`。
     * 注意，即使删除执行成功，删除的行数也可能为 0。
     * @throws StaleObjectException
     */
    protected function deleteInternal()
    {
        if (!$this->beforeDelete()) {
            return false;
        }

        // we do not check the return value of deleteAll() because it's possible
        // the record is already deleted in the database and thus the method will return 0
        $condition = $this->getOldPrimaryKey(true);
        $lock = $this->optimisticLock();
        if ($lock !== null) {
            $condition[$lock] = $this->$lock;
        }
        $result = static::deleteAll($condition);
        if ($lock !== null && !$result) {
            throw new StaleObjectException('The object being deleted is outdated.');
        }
        $this->setOldAttributes(null);
        $this->afterDelete();

        return $result;
    }

    /**
     * 返回一个给定值，指示给定的活动记录是否与当前记录相同。
     * 通过比较两个活动记录的表名和主键值来进行比较。
     * 如果其中一个记录 [[isNewRecord|is new]] 也会被认为不相同。
     * @param ActiveRecord $record 要比较记录
     * @return bool 两个活动记录是否引用同一数据库表中的同一行。
     */
    public function equals($record)
    {
        if ($this->isNewRecord || $record->isNewRecord) {
            return false;
        }

        return static::tableName() === $record->tableName() && $this->getPrimaryKey() === $record->getPrimaryKey();
    }

    /**
     * 返回一个值，该值表示指定的操作在当前 [[$scenario]] 中是否为事务性操作。
     * @param int $operation 要检查的操作。可能的值为 [[OP_INSERT]]，[[OP_UPDATE]] 以及 [[OP_DELETE]]。
     * @return bool 指定的操作在当前 [[scenario]] 中是否是事务性的。
     */
    public function isTransactional($operation)
    {
        $scenario = $this->getScenario();
        $transactions = $this->transactions();

        return isset($transactions[$scenario]) && ($transactions[$scenario] & $operation);
    }
}
