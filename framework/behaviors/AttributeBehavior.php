<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\behaviors;

use Closure;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;

/**
 * 当某个事件发生的时候，AttributeBehavior 可以自动地给某个或多个 ActiveRecord 对象的属性
 * 分配指定的值。
 *
 * 在使用 AttributeBehavior 的时候，需要配置 [[attributes]] 属性，这个属性应该指明需要更新的属性列表
 * 和触发更新时对应的事件。然后配置 [[value]] 属性为一个 PHP 回调函数
 * 该回调函数返回要分配给当前属性的值。
 * 比如，
 *
 * ```php
 * use yii\behaviors\AttributeBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         [
 *             'class' => AttributeBehavior::className(),
 *             'attributes' => [
 *                 ActiveRecord::EVENT_BEFORE_INSERT => 'attribute1',
 *                 ActiveRecord::EVENT_BEFORE_UPDATE => 'attribute2',
 *             ],
 *             'value' => function ($event) {
 *                 return 'some value';
 *             },
 *         ],
 *     ];
 * }
 * ```
 *
 * 因为属性值是由行为自动设置的，所以通常不需要用户输入，因此
 * 也不需要验证等，这些属性不应该出现在 [[\yii\base\Model::rules()|rules()]] 方法中。
 *
 * @author Luciano Baraglia <luciano.baraglia@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AttributeBehavior extends Behavior
{
    /**
     * @var array 属性列表，属性的值将由 [[value]] 自动填充。
     * 数组的键是 ActiveRecord 的事件，属性就是更新于这些事件之上，
     * 数组的值就是要更新的属性。
     * 你可以用字符串来表示一个单独的属性也可以用一个数组来表示一系列属性。比如，
     *
     * ```php
     * [
     *     ActiveRecord::EVENT_BEFORE_INSERT => ['attribute1', 'attribute2'],
     *     ActiveRecord::EVENT_BEFORE_UPDATE => 'attribute2',
     * ]
     * ```
     */
    public $attributes = [];
    /**
     * @var mixed 要分配给当前属性的值。它可以是一个匿名函数，
     * 数组格式的 callable（比如 `[$this, 'methodName']`），一个 [[\yii\db\Expression|Expression]] 对象表示的 DB 表达式
     * （比如 `new Expression('NOW()')`），标量，字符串或者一个任意的值。
     * 如果是前者，函数的返回值将会设置给这些属性。
     * 函数的签名应该像下面这样，
     *
     * ```php
     * function ($event)
     * {
     *     // 返回值将会设置到当前的属性
     * }
     * ```
     */
    public $value;
    /**
     * @var bool 当 `$owner`
     * 没有更新的时候是否跳过这个行为
     * @since 2.0.8
     */
    public $skipUpdateOnClean = true;
    /**
     * @var bool 是否保留非空的属性值
     * @since 2.0.13
     */
    public $preserveNonEmptyValues = false;


    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return array_fill_keys(
            array_keys($this->attributes),
            'evaluateAttributes'
        );
    }

    /**
     * 计算属性的值并分配给当前属性。
     * @param Event $event
     */
    public function evaluateAttributes($event)
    {
        if ($this->skipUpdateOnClean
            && $event->name == ActiveRecord::EVENT_BEFORE_UPDATE
            && empty($this->owner->dirtyAttributes)
        ) {
            return;
        }

        if (!empty($this->attributes[$event->name])) {
            $attributes = (array) $this->attributes[$event->name];
            $value = $this->getValue($event);
            foreach ($attributes as $attribute) {
                // 忽略属性名不是字符串的情况（比如当被 TimestampBehavior::updatedAtAttribute 设置时）
                if (is_string($attribute)) {
                    if ($this->preserveNonEmptyValues && !empty($this->owner->$attribute)) {
                        continue;
                    }
                    $this->owner->$attribute = $value;
                }
            }
        }
    }

    /**
     * 返回给当前属性准备的值。
     * 该方法在 [[evaluateAttributes()]] 里调用。
     * 它的返回值将会设置到对应触发事件的属性上。
     * @param Event $event 触发当前属性更新的事件
     * @return mixed 属性值
     */
    protected function getValue($event)
    {
        if ($this->value instanceof Closure || (is_array($this->value) && is_callable($this->value))) {
            return call_user_func($this->value, $event);
        }

        return $this->value;
    }
}
