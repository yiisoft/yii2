<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\behaviors;

use yii\base\Behavior;
use yii\base\InvalidArgumentException;
use yii\base\Model;
use yii\db\BaseActiveRecord;
use yii\helpers\StringHelper;
use yii\validators\BooleanValidator;
use yii\validators\NumberValidator;
use yii\validators\StringValidator;

/**
 * AttributeTypecastBehavior 提供了模型属性自动转换数据类型的能力。
 * 这个行为在数据库语法比较弱化的数据库系统上使用 ActiveRecord 时比较有用，比如 MongoDB 或者 Redis 这些数据库。
 * 它也可以在普通的 [[\yii\db\ActiveRecord]] 甚至 [[\yii\base\Model]] 上发挥作用。
 * 因为它能够在执行模型验证之后保持严格的属性数据类型。
 *
 * 这个行为应该附加到 [[\yii\base\Model]] 或者 [[\yii\db\BaseActiveRecord]] 的子类中使用。
 *
 * 你应该通过 [[attributeTypes]] 指明确切的数据类型。
 *
 * 比如：
 *
 * ```php
 * use yii\behaviors\AttributeTypecastBehavior;
 *
 * class Item extends \yii\db\ActiveRecord
 * {
 *     public function behaviors()
 *     {
 *         return [
 *             'typecast' => [
 *                 'class' => AttributeTypecastBehavior::className(),
 *                 'attributeTypes' => [
 *                     'amount' => AttributeTypecastBehavior::TYPE_INTEGER,
 *                     'price' => AttributeTypecastBehavior::TYPE_FLOAT,
 *                     'is_active' => AttributeTypecastBehavior::TYPE_BOOLEAN,
 *                 ],
 *                 'typecastAfterValidate' => true,
 *                 'typecastBeforeSave' => false,
 *                 'typecastAfterFind' => false,
 *             ],
 *         ];
 *     }
 *
 *     // ...
 * }
 * ```
 *
 * Tip: 你可以把 [[attributeTypes]] 留空，
 * 这时行为将通过属主组件的验证规则自动组装它的值。
 * 下面的例子展示了 [[attributeTypes]] 是根据它上面的 rules 方法里的验证规则创建了一模一样的数据类型：
 *
 * ```php
 * use yii\behaviors\AttributeTypecastBehavior;
 *
 * class Item extends \yii\db\ActiveRecord
 * {
 *
 *     public function rules()
 *     {
 *         return [
 *             ['amount', 'integer'],
 *             ['price', 'number'],
 *             ['is_active', 'boolean'],
 *         ];
 *     }
 *
 *     public function behaviors()
 *     {
 *         return [
 *             'typecast' => [
 *                 'class' => AttributeTypecastBehavior::className(),
 *                 // 'attributeTypes' will be composed automatically according to `rules()`
 *             ],
 *         ];
 *     }
 *
 *     // ...
 * }
 * ```
 *
 * 这个行为允许自动类型转换发生在如下的场景：
 *
 * - 在成功通过模型验证之后
 * - 在模型保存之前（插入或者更新）
 * - 在模型查找之后（通过查询语句找到模型或模型执行刷新）
 *
 * 你可以通过使用 [[typecastAfterValidate]]，[[typecastBeforeSave]] 和 [[typecastAfterFind]]
 * 来控制自动转换发生在哪些指定的场景。
 * 默认情况下只在模型成功通过验证之后进行类型转换。
 *
 * Note: 你也可以在任何时候手动地通过调用 [[typecastAttributes()]] 方法触发属性的类型转换：
 *
 * ```php
 * $model = new Item();
 * $model->price = '38.5';
 * $model->is_active = 1;
 * $model->typecastAttributes();
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.10
 */
class AttributeTypecastBehavior extends Behavior
{
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_STRING = 'string';

    /**
     * @var Model|BaseActiveRecord 行为的属主。
     */
    public $owner;
    /**
     * @var array 属性进行类型转换的格式：attributeName => type。
     * Type 可以是一个 PHP 匿名函数，
     * 它接收属性的原始值作为参数并且应该返回类型转换的结果。
     * 比如：
     *
     * ```php
     * [
     *     'amount' => 'integer',
     *     'price' => 'float',
     *     'is_active' => 'boolean',
     *     'date' => function ($value) {
     *         return ($value instanceof \DateTime) ? $value->getTimestamp(): (int)$value;
     *     },
     * ]
     * ```
     *
     * 如果没有设置 $attributeTypes，属性类型映射将会根据属主组件的验证规则自动组装。
     */
    public $attributeTypes;
    /**
     * @var bool 是否跳过 `null` 值的类型转换。
     * 如果开启，属性值等于 `null` 时将不会执行类型转换（也就是说 `null` 还保持为 `null` ）；
     * 如果不开启，他将根据 [[attributeTypes]] 里的类型配置执行转换。
     */
    public $skipOnNull = true;
    /**
     * @var bool 是否在通过属主模型验证之后执行类型转换。
     * 注意，类型转换只有在模型验证成功之后才执行。
     * 也就是说，属主模型没有验证出错。
     * 注意，在该行为已经附加到属主模型之后再调整该选项的值不会起作用。
     */
    public $typecastAfterValidate = true;
    /**
     * @var bool 是否在保存属主模型之前执行类型转换（插入或更新）。
     * 为了追求较好的性能该选项可以设置为 false。
     * 比如，在使用 [[\yii\db\ActiveRecord]] 的时候，在保存之前执行类型转换没什么意义，
     * 因此可以设置为 false。
     * 注意，在该行为已经附加到属主模型之后再调整该选项的值不会起作用。
     */
    public $typecastBeforeSave = false;
    /**
     * @var bool 是否在保存属主模型之后执行类型转换（插入或更新）。
     * 为了追求较好的性能该选项可以设置为 false。
     * 比如，在使用 [[\yii\db\ActiveRecord]] 的时候，在保存之后执行类型转换没什么意义，
     * 因此可以设置为 false。
     * 注意，在该行为已经附加到属主模型之后再调整该选项的值不会起作用。
     * @since 2.0.14
     */
    public $typecastAfterSave = false;
    /**
     * @var bool 是否在从数据库获取到属主模型数据之后，
     * 执行类型转换（获取模型或模型刷新）。
     * 为了追求较好的性能该选项可以设置为 false。
     * 比如，在使用 [[\yii\db\ActiveRecord]] 的时候，获取模型数据之后执行类型转换大多数情况下没什么意义，
     * 因此可以设置为 false。
     * 注意，在该行为已经附加到属主模型之后再调整该选项的值不会起作用。
     */
    public $typecastAfterFind = false;

    /**
     * @var array 自动检测 [[attributeTypes]] 时的内部静态缓存值。
     * 格式是： ownerClassName => attributeTypes
     */
    private static $autoDetectedAttributeTypes = [];


    /**
     * 针对所有的属主类，
     * 清除自动检测 [[attributeTypes]] 时的内部静态缓存值
     */
    public static function clearAutoDetectedAttributeTypes()
    {
        self::$autoDetectedAttributeTypes = [];
    }

    /**
     * {@inheritdoc}
     */
    public function attach($owner)
    {
        parent::attach($owner);

        if ($this->attributeTypes === null) {
            $ownerClass = get_class($this->owner);
            if (!isset(self::$autoDetectedAttributeTypes[$ownerClass])) {
                self::$autoDetectedAttributeTypes[$ownerClass] = $this->detectAttributeTypes();
            }
            $this->attributeTypes = self::$autoDetectedAttributeTypes[$ownerClass];
        }
    }

    /**
     * 根据 [[attributeTypes]] 执行属主属性的类型转换。
     * @param array $attributeNames 给出想要执行类型转换的属性名列表。
     * 如果这个参数为空，
     * 那么列在 [[attributeTypes]] 之内的任何一个属性都执行类型转换。
     */
    public function typecastAttributes($attributeNames = null)
    {
        $attributeTypes = [];

        if ($attributeNames === null) {
            $attributeTypes = $this->attributeTypes;
        } else {
            foreach ($attributeNames as $attribute) {
                if (!isset($this->attributeTypes[$attribute])) {
                    throw new InvalidArgumentException("There is no type mapping for '{$attribute}'.");
                }
                $attributeTypes[$attribute] = $this->attributeTypes[$attribute];
            }
        }

        foreach ($attributeTypes as $attribute => $type) {
            $value = $this->owner->{$attribute};
            if ($this->skipOnNull && $value === null) {
                continue;
            }
            $this->owner->{$attribute} = $this->typecastValue($value, $type);
        }
    }

    /**
     * 把指定的值转换为指定的数据类型。
     * @param mixed $value 将有执行类型转换的值。
     * @param string|callable $type 类型名或者能够执行类型转换的匿名函数。
     * @return mixed 类型转换后的结果。
     */
    protected function typecastValue($value, $type)
    {
        if (is_scalar($type)) {
            if (is_object($value) && method_exists($value, '__toString')) {
                $value = $value->__toString();
            }

            switch ($type) {
                case self::TYPE_INTEGER:
                    return (int) $value;
                case self::TYPE_FLOAT:
                    return (float) $value;
                case self::TYPE_BOOLEAN:
                    return (bool) $value;
                case self::TYPE_STRING:
                    if (is_float($value)) {
                        return StringHelper::floatToString($value);
                    }
                    return (string) $value;
                default:
                    throw new InvalidArgumentException("Unsupported type '{$type}'");
            }
        }

        return call_user_func($type, $value);
    }

    /**
     * 从属主模型的验证规则里组装 [[attributeTypes]] 留空时的默认值。
     * @return array 属性类型映射。
     */
    protected function detectAttributeTypes()
    {
        $attributeTypes = [];
        foreach ($this->owner->getValidators() as $validator) {
            $type = null;
            if ($validator instanceof BooleanValidator) {
                $type = self::TYPE_BOOLEAN;
            } elseif ($validator instanceof NumberValidator) {
                $type = $validator->integerOnly ? self::TYPE_INTEGER : self::TYPE_FLOAT;
            } elseif ($validator instanceof StringValidator) {
                $type = self::TYPE_STRING;
            }

            if ($type !== null) {
                foreach ((array) $validator->attributes as $attribute) {
                    $attributeTypes[ltrim($attribute, '!')] = $type;
                }
            }
        }

        return $attributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function events()
    {
        $events = [];

        if ($this->typecastAfterValidate) {
            $events[Model::EVENT_AFTER_VALIDATE] = 'afterValidate';
        }
        if ($this->typecastBeforeSave) {
            $events[BaseActiveRecord::EVENT_BEFORE_INSERT] = 'beforeSave';
            $events[BaseActiveRecord::EVENT_BEFORE_UPDATE] = 'beforeSave';
        }
        if ($this->typecastAfterSave) {
            $events[BaseActiveRecord::EVENT_AFTER_INSERT] = 'afterSave';
            $events[BaseActiveRecord::EVENT_AFTER_UPDATE] = 'afterSave';
        }
        if ($this->typecastAfterFind) {
            $events[BaseActiveRecord::EVENT_AFTER_FIND] = 'afterFind';
        }

        return $events;
    }

    /**
     * 响应属主 'afterValidate' 事件的方法，确保属性的类型转换。
     * @param \yii\base\Event $event 事件对象。
     */
    public function afterValidate($event)
    {
        if (!$this->owner->hasErrors()) {
            $this->typecastAttributes();
        }
    }

    /**
     * 响应属主 'beforeInsert' 和 'beforeUpdate' 事件的方法，确保属性的类型转换。
     * @param \yii\base\Event $event 事件对象。
     */
    public function beforeSave($event)
    {
        $this->typecastAttributes();
    }
    
    /**
     * 响应属主 'afterInsert' 和 'afterUpdate' 事件的方法，确保属性的类型转换。
     * @param \yii\base\Event $event 事件对象。
     * @since 2.0.14
     */
    public function afterSave($event)
    {
        $this->typecastAttributes();
    }

    /**
     * 响应属主 'afterFind' 事件的方法，确保属性的类型转换。
     * @param \yii\base\Event $event 事件对象。
     */
    public function afterFind($event)
    {
        $this->typecastAttributes();
    }
}
