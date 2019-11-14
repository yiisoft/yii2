<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\StaticInstanceInterface;

/**
 * ActiveRecordInterface.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
interface ActiveRecordInterface extends StaticInstanceInterface
{
    /**
     * 返回此 AR 类的主键 **name(s)**。
     *
     * 注意，即使记录只有一个主键，也应该返回一个数组。
     *
     * 对于主键 **value** 请参阅 [[getPrimaryKey()]]。
     *
     * @return string[] 此 AR 类的主键名称。
     */
    public static function primaryKey();

    /**
     * 返回记录的所有属性列表名称。
     * @return array 属性名称列表。
     */
    public function attributes();

    /**
     * 返回指定的属性值。
     * 如果此记录是查询的结果并且未加载该属性，
     * 将会返回 `null`。
     * @param string $name 属性名
     * @return mixed 属性值。如果属性未设置或者不存在，返回 `null`。
     * @see hasAttribute()
     */
    public function getAttribute($name);

    /**
     * 设置指定的属性值。
     * @param string $name 属性名。
     * @param mixed $value 属性值。
     * @see hasAttribute()
     */
    public function setAttribute($name, $value);

    /**
     * 返回一个值，表示记录是否具有指定名称的属性。
     * @param string $name 属性名
     * @return bool 记录是否具有指定名称的属性。
     */
    public function hasAttribute($name);

    /**
     * 返回主键的值。
     * @param bool $asArray 是否将主键值作为数组返回。
     * 如果为 true，返回值将是一个数组，属性名称为键，属性值为值。
     * 请注意，对于复合主键，无论此参数值如何，都将始终返回一个数组。
     * @return mixed 主键值。如果主键是复合键或 `$asArray` 为 true，
     * 则返回数组（attribute name => attribute value）。
     * 否则返回一个字符串（如果键值为 `null`，则返回 `null`）。
     */
    public function getPrimaryKey($asArray = false);

    /**
     * 返回旧的主键值。
     * 这是指在执行 find 方法，
     * （例如 find()，findOne()）后填充到记录中的主键值。
     * 即使主键属性是手动指定的不同的值，该值仍然保持不变。
     * @param bool $asArray 是否将主键值作为一个数组返回。
     * 如果为 true，返回值将是一个数组，属性名称为键，属性值为值。
     * 如果为 `false`（默认值），将为非复合主键返回标量值。
     * @property mixed 旧的主键值。
     * 如果主键是复合键，则返回一个数组（column name => column value）。
     * 否则返回一个字符串（如果键值为 `null`，则返回 `null`）。
     * @return mixed 旧的主键值。
     * 如果主键是复合键或 `$asArray` 为 true，则返回数组（column name => column value）。
     * 否则将返回一个字符串（如果键值为 `null`，则返回 `null`）。
     */
    public function getOldPrimaryKey($asArray = false);

    /**
     * 返回一个表明给定的属性集是否是该模型的主键的值。
     * @param array $keys 要检查的属性集
     * @return bool 给定的属性集是否是此模型的主键
     */
    public static function isPrimaryKey($keys);

    /**
     * 创建用来查询的 [[ActiveQueryInterface]] 实例。
     *
     * 返回的 [[ActiveQueryInterface]] 实例可以通过调用 `one()` 或 `all()` 之前调用
     * [[ActiveQueryInterface]] 方法来进一步自定义，
     * 以返回填充的 ActiveRecord 实例。例如，
     *
     * ```php
     * // 找到 ID 为 1 的客户
     * $customer = Customer::find()->where(['id' => 1])->one();
     *
     * // 查找所有活跃客户并按其年龄排序
     * $customers = Customer::find()
     *     ->where(['status' => 1])
     *     ->orderBy('age')
     *     ->all();
     * ```
     *
     * [[BaseActiveRecord::hasOne()]] 和 [[BaseActiveRecord::hasMany()]]
     * 也调用此方法来创建关系查询。
     *
     * 你可以覆盖此方法以返回自定义查询。例如，
     *
     * ```php
     * class Customer extends ActiveRecord
     * {
     *     public static function find()
     *     {
     *         // use CustomerQuery instead of the default ActiveQuery
     *         return new CustomerQuery(get_called_class());
     *     }
     * }
     * ```
     *
     * 以下代码显示如何为所有查询应用默认条件：
     *
     * ```php
     * class Customer extends ActiveRecord
     * {
     *     public static function find()
     *     {
     *         return parent::find()->where(['deleted' => false]);
     *     }
     * }
     *
     * // 使用 andWhere()/orWhere() 应用默认条件
     * // SELECT FROM customer WHERE `deleted`=:deleted AND age>30
     * $customers = Customer::find()->andWhere('age>30')->all();
     *
     * // 使用 where() 忽略默认条件
     * // SELECT FROM customer WHERE age>30
     * $customers = Customer::find()->where('age>30')->all();
     *
     * @return ActiveQueryInterface 新创建的 [[ActiveQueryInterface]] 实例。
     */
    public static function find();

    /**
     * 通过主键或列值数组返回单个活动记录模型实例。
     *
     * 该方法接受：
     *
     *  - 标量值（integer 或 string）：按单个主键值查询并返回记录
     *    （如果未找到就返回 `null`）。
     *  - 一个非关联数组：按主键值列表查询并返回第一条记录
     *    （如果未找到就返回 `null`）。
     *  - 一个键值对：通过一组属性值进行查询，并返回匹配所有属性值的单个记录（如果未找到就返回 `null`）。
     *    注意，`['id' => 1, 2]` 被视为非关联数组。
     *    列名仅限于 SQL DBMS 的当前记录列表，或者过滤为限制为简单的过滤条件。
     *
     * 该方法将自动调用 `one()` 方法并且返回 [[ActiveRecordInterface|ActiveRecord]]
     * 实例。
     *
     * > Note: 因为只是一种简单的方法，所以使用更复杂的条件，比如 ['!=', 'id', 1] 将不起作用。
     * > 如果需要指定更复杂的条件，请将 [[find()]] 与 [[ActiveQuery::where()|where()]] 组合使用。
     *
     * 有关用法的示例，请参阅以下代码：
     *
     * ```php
     * // 找到主键值为 10 的单个客户
     * $customer = Customer::findOne(10);
     *
     * // 上面的代码相当于：
     * $customer = Customer::find()->where(['id' => 10])->one();
     *
     * // 找到主键值为 10，11 或 12 的客户。
     * $customers = Customer::findOne([10, 11, 12]);
     *
     * // 上面的代码相当于：
     * $customers = Customer::find()->where(['id' => [10, 11, 12]])->one();
     *
     * // 找到第一个年龄为 30 岁且状态为 1 的客户
     * $customer = Customer::findOne(['age' => 30, 'status' => 1]);
     *
     * // 上面的代码相当于：
     * $customer = Customer::find()->where(['age' => 30, 'status' => 1])->one();
     * ```
     *
     * 如果需要将用户输入传递给此方法，在输入值是标量或在数组条件情况下，
     * 确保无法从外部更改数组结构：
     *
     * ```php
     * // yii\web\Controller ensures that $id is scalar
     * public function actionView($id)
     * {
     *     $model = Post::findOne($id);
     *     // ...
     * }
     *
     * // 显示指定要搜索的列，在此处传递标量或数组将始终导致查询单个记录
     * $model = Post::findOne(['id' => Yii::$app->request->get('id')]);
     *
     * // 不要使用以下代码！可以通过任意列值注入数组条件来过滤！
     * $model = Post::findOne(Yii::$app->request->get('id'));
     * ```
     *
     * @param mixed $condition 主键值或一组列值。
     * @return static 与条件匹配的 ActiveRecord 实例，如果没有匹配则为 `null`。
     */
    public static function findOne($condition);

    /**
     * 返回与指定的主键值或一组列值匹配的活动记录模型的列表。
     *
     * The method accepts:
     *
     *  - 标量值（integer 或 string）：通过单个主键值进行查询并返回包含相应记录的数组
     *    （如果未找到则返回空数组）。
     *  - 非关联数组：按主键值列表查询并返回相应记录
     *    （如果没有找到则返回空数组）。
     *    注意，空条件将导致空结果，
     *    因为它将被解释为搜索结果主键而不是空的 `WHERE` 条件。
     *  - 一个键值对关联数组：通过一组属性值进行查询，
     *    并返回所有属性值匹配的记录数组（如果没有找到则返回空数组）
     *    请注意，`['id' => 1, 2]` 被视为非关联数组。
     *    列名仅限于 SQL DBMS 的当前记录表列，否则将过滤以限制为简单的过滤条件。
     *
     * 此方法将自动调用 `all()` 方法并返回 [[ActiveRecordInterface|ActiveRecord]]
     * 实例的数组。
     *
     * > Note：因为只是一种简单的方法，所以使用更复杂的条件，比如 ['!=', 'id', 1] 将不起作用。
     * > 如果需要指定更复杂的条件，请将 [[find()]] 与 [[ActiveQuery::where()|where()]] 组合使用。
     *
     * 有关于用法示例，请参阅以下代码：
     *
     * ```php
     * // 找到主键值为 10 的客户
     * $customers = Customer::findAll(10);
     *
     * // 上面的代码相当于：
     * $customers = Customer::find()->where(['id' => 10])->all();
     *
     * // 找到主键值为 10，11 或 12 的客户。
     * $customers = Customer::findAll([10, 11, 12]);
     *
     * // 上面的代码相当于：
     * $customers = Customer::find()->where(['id' => [10, 11, 12]])->all();
     *
     * // 找到年龄为 30 岁且状态为 1 的客户
     * $customers = Customer::findAll(['age' => 30, 'status' => 1]);
     *
     * // 上面的代码相当于：
     * $customers = Customer::find()->where(['age' => 30, 'status' => 1])->all();
     * ```
     *
     * 如果需要将用户输入传递给此方法，在输入值是标量或在数组条件情况下，
     * 确保无法从外部更改数组数组结构：
     *
     * ```php
     * // yii\web\Controller ensures that $id is scalar
     * public function actionView($id)
     * {
     *     $model = Post::findOne($id);
     *     // ...
     * }
     *
     * // 显式指定要搜索的列，在此处传递标量或数组将始终导致查找单个记录
     * $model = Post::findOne(['id' => Yii::$app->request->get('id')]);
     *
     * // 不要使用以下代码！可以通过任意列值注入数组条件来过滤！
     * $model = Post::findOne(Yii::$app->request->get('id'));
     * ```
     *
     * @param mixed $condition 主键值或一组列值
     * @return array 一个 ActiveRecord 实例数组，如果没有匹配则为空数组。
     */
    public static function findAll($condition);

    /**
     * 使用提供的属性值和条件更新记录。
     *
     * 例如，要将状态为 2 的所有客户的状态更改为 1：
     *
     * ```php
     * Customer::updateAll(['status' => 1], ['status' => '2']);
     * ```
     *
     * @param array $attributes 要为记录保存的属性值（键值对）。
     * 与 [[update()]] 不同，这些不会被验证。
     * @param array $condition 与应更新的记录匹配的条件。
     * 有关于如何指定此参数，请参阅 [[QueryInterface::where()]]。
     * 空条件将匹配所有记录。
     * @return int 更新的行数
     */
    public static function updateAll($attributes, $condition = null);

    /**
     * 使用提供的条件删除记录。
     * WARNING：如果未指定任何条件，则此方法将删除表中的所有行。
     *
     * 例如，要删除状态为 3 的所有客户：
     *
     * ```php
     * Customer::deleteAll([status = 3]);
     * ```
     *
     * @param array $condition 与应删除的记录匹配的条件。
     * 有关于如果指定参数，请参阅 [[QueryInterface::where()]]。
     * 空条件将匹配所有记录。
     * @return int the number of rows deleted
     */
    public static function deleteAll($condition = null);

    /**
     * 保存当前记录。
     *
     * 当 [[getIsNewRecord()|isNewRecord]] 为 true 时，将调用 [[insert()]]，
     * 当 [[getIsNewRecord()|isNewRecord]] 为 false 时，将调用 [[update()]]。
     *
     * 例如，要保存客户记录：
     *
     * ```php
     * $customer = new Customer; // or $customer = Customer::findOne($id);
     * $customer->name = $name;
     * $customer->email = $email;
     * $customer->save();
     * ```
     *
     * @param bool $runValidation 在保存记录之前，
     * 是否执行验证（调用 [[\yii\base\Model::validate()|validate()]]）。默认为 `true`。
     * 如果验证失败，则记录将不会保存到数据库中，并且此方法将返回 `false`。
     * @param array $attributeNames 需要保存的属性名称列表。
     * 默认为 `null`，表示将保存从 DB 加载的所有属性。
     * @return bool 是否保存成功（即没有发生验证错误）。
     */
    public function save($runValidation = true, $attributeNames = null);

    /**
     * 使用此记录的属性值将记录插入数据库。
     *
     * 用法示例：
     *
     * ```php
     * $customer = new Customer;
     * $customer->name = $name;
     * $customer->email = $email;
     * $customer->insert();
     * ```
     *
     * @param bool $runValidation 在保存记录之前，
     * 是否执行验证（调用 [[\yii\base\Model::validate()|validate()]]）。默认为 `true`。
     * 如果验证失败，则记录将不会保存到数据库中，并且此方法将返回 `false`。
     * @param array $attributes 需要保存的属性名称列表。
     * 默认为 `null`，表示将保存从 DB 加载的所有属性。
     * @return bool 属性是否有效并且记录是否已成功插入。
     */
    public function insert($runValidation = true, $attributes = null);

    /**
     * 将对此活动记录的更改保存到数据库中。
     *
     * 用法示例：
     *
     * ```php
     * $customer = Customer::findOne($id);
     * $customer->name = $name;
     * $customer->email = $email;
     * $customer->update();
     * ```
     *
     * @param bool $runValidation 在保存记录之前，
     * 是否执行验证（调用 [[\yii\base\Model::validate()|validate()]]）。默认为 `true`。
     * 如果验证失败，则记录将不会保存到数据库中，并且此方法将返回 `false`。
     * @param array $attributeNames 需要保存的属性列表。
     * 默认为 `null`，表示将保存从 DB 加载的所有属性。
     * @return int|bool 影响的行数，
     * 如果验证失败或者由于其他原因停止更新过程，返回 `false`。
     * 注意，即使更新成功，
     * 受影响的行数也有可能为 0。
     */
    public function update($runValidation = true, $attributeNames = null);

    /**
     * 从数据库删除记录。
     *
     * @return int|bool 删除的行数，如果由于某种原因删除失败，则为 `false` 。
     * 注意，即使删除执行成功，删除的行数也可能是 0。
     */
    public function delete();

    /**
     * 返回一个表示当前记录是否为新纪录的值（未保存在数据库中）。
     * @return bool 是否记录是新的，应在调用 [[save()]] 时插入。
     */
    public function getIsNewRecord();

    /**
     * 返回一个表示给定的记录是否与当前记录相同的值。
     * 两个 [[getIsNewRecord()|new]] 记录被认为是不相等的。
     * @param static $record 要比较的记录
     * @return bool 两个活动记录是否引用同一个数据表中的同一行。
     */
    public function equals($record);

    /**
     * 返回具有指定名称的关系对象。
     * 关系由 getter 方法定义，
     * 该方法返回实现 [[ActiveQueryInterface]] 的对象（通常这将是 [[ActiveQuery]] 对象）。
     * 它可以在 ActiveRecord 类本身或其行为之一中申明。
     * @param string $name 关系名称，例如 `orders` 用于通过 `getOrders()` 方法定义的关系（区分大小写）。
     * @param bool $throwException 如果关系不存在，是否抛出异常。
     * @return ActiveQueryInterface 关系查询对象
     */
    public function getRelation($name, $throwException = true);

    /**
     * 使用相关记录填充命名关系。
     * 注意，此方法不会检查关系是否存在。
     * @param string $name 关系名称，例如 `orders` 用于通过 `getOrders()` 方法定义的关系（区分大小写）。
     * @param ActiveRecordInterface|array|null $records 要填充到关系中的相关记录。
     * @since 2.0.8
     */
    public function populateRelation($name, $records);

    /**
     * 建立两条记录之间的关系。
     *
     * 通过将一个记录中的外键值设置为另外一个记录中相应的主键值，
     * 从而建立该关系。
     * 具有外键的记录将被保存到数据库中而不进行验证。
     *
     * 如果这个关系涉及到一个连接表，
     * 那么一个新的行将被插入到包含来自两个记录的主键值的连接表中。
     *
     * 方法要求主键值不能为 `null`。
     *
     * @param string $name 区分大小写的关系名称，例如 `orders` 用于通过 `getOrders()` 方法定义的关系。
     * @param static $model 与当前记录链接的记录。
     * @param array $extraColumns 要保存的连接表中的附加列值。
     * 此参数仅对涉及连接表的关系（即，
     * 使用 [[ActiveQueryInterface::via()]] 设置的关系）有意义。
     */
    public function link($name, $model, $extraColumns = []);

    /**
     * 破坏两条记录之间的关系。
     *
     * 如果 `$delete` 为真，则删除具有关系外键的记录。
     * 否则，外键将被设置为 `null`，并且将保存记录而不进行验证。
     *
     * @param string $name 区分大小写的关系名称，例如 `orders` 用于通过 `getOrders()` 方法定义的关系。
     * @param static $model 该模型与当前模型无关。
     * @param bool $delete 是否删除包含外键的模型。
     * 如果为 false，模型的外键将设置为 `null` 并保存。
     * 如果为 true，包含外键的模型将被删除。
     */
    public function unlink($name, $model, $delete = false);

    /**
     * 返回此 AR 类使用的连接。
     * @return mixed 此 AR 类使用的数据库连接。
     */
    public static function getDb();
}
