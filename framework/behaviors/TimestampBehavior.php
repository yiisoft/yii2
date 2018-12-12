<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\behaviors;

use yii\base\InvalidCallException;
use yii\db\BaseActiveRecord;

/**
 * TimestampBehavior 用来自动给指定的属性填充当前时间戳。 
 *
 * 要使用 TimestampBehavior，把下面的代码加到你的 ActiveRecord 类中：
 *
 * ```php
 * use yii\behaviors\TimestampBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         TimestampBehavior::className(),
 *     ];
 * }
 * ```
 *
 * 默认情况下，当关联的 AR 对象执行插入操作时，TimestampBehavior 将会给 `created_at` 和 `updated_at`
 * 两个属性赋值为当前时间戳；而当 AR 对象执行更新操作时，
 * 它只给 `updated_at` 属性赋值为当前时间戳。时间戳的值来自于 `time()`。
 *
 * 由于属性值是被这个行为自动设置，所以属性值不必用户输入也因此没有必要验证。
 * 因此，`created_at` 和 `updated_at` 这两个属性不应该出现在 [[\yii\base\Model::rules()|rules()]] 这个模型方法中。
 *
 * 对于应用在 MySQL 数据库的上述实现，请声明 columns(`created_at`, `updated_at`) 为整型来存储时间戳。
 *
 * 如果你的属性名不一样或者你想用不同的方式计算时间戳，
 * 那么你可以像下面这样配置 [[createdAtAttribute]]，[[updatedAtAttribute]] 和 [[value]] 来达到目的。
 *
 * ```php
 * use yii\db\Expression;
 *
 * public function behaviors()
 * {
 *     return [
 *         [
 *             'class' => TimestampBehavior::className(),
 *             'createdAtAttribute' => 'create_time',
 *             'updatedAtAttribute' => 'update_time',
 *             'value' => new Expression('NOW()'),
 *         ],
 *     ];
 * }
 * ```
 *
 * 如果你像上述例子那样使用了 [[\yii\db\Expression]] 对象，那么当记录保存后
 * 属性的值将不是时间戳而是这个 Expression 对象本身。
 * 如果你随后需要这个值的话，应该先调用这个记录的 [[\yii\db\ActiveRecord::refresh()|refresh()]] 方法。
 *
 * TimestampBehavior 也提供了一个 [[touch()]] 方法，
 * 它可以给指定的一个或多个属性设置为当前时间戳然后保存入库。比如，
 *
 * ```php
 * $model->touch('creation_time');
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class TimestampBehavior extends AttributeBehavior
{
    /**
     * @var string 接收时间戳的属性名。
     * 如果你不想记录生成时间的话把它设置为false。
     */
    public $createdAtAttribute = 'created_at';
    /**
     * @var string 接收时间戳的属性名。
     * 如果你不想记录更新时间的话把它设置为false。
     */
    public $updatedAtAttribute = 'updated_at';
    /**
     * {@inheritdoc}
     *
     * 如果这个值是 `null`，
     * 那么它将用 PHP 函数 [time()](http://php.net/manual/en/function.time.php) 作为 $value 的值。
     */
    public $value;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [
                BaseActiveRecord::EVENT_BEFORE_INSERT => [$this->createdAtAttribute, $this->updatedAtAttribute],
                BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->updatedAtAttribute,
            ];
        }
    }

    /**
     * {@inheritdoc}
     *
     * 如果这个值是 `null`，
     * 那么它将用 PHP 函数 [time()](http://php.net/manual/en/function.time.php) 作为 $value 的值。
     */
    protected function getValue($event)
    {
        if ($this->value === null) {
            return time();
        }

        return parent::getValue($event);
    }

    /**
     * 把指定的属性更新为当前时间戳。
     *
     * ```php
     * $model->touch('lastVisit');
     * ```
     * @param string $attribute 要更新的属性名。
     * @throws InvalidCallException 如果行为的拥有者（ AR 对象）是一个未更新过的对象 (since version 2.0.6)。
     */
    public function touch($attribute)
    {
        /* @var $owner BaseActiveRecord */
        $owner = $this->owner;
        if ($owner->getIsNewRecord()) {
            throw new InvalidCallException('Updating the timestamp is not possible on a new record.');
        }
        $owner->updateAttributes(array_fill_keys((array) $attribute, $this->getValue(null)));
    }
}
