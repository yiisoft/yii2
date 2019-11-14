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
 * AttributesBehavior 是在某个事件发生的时候，
 * 用来给 ActiveRecord 对象的一个或多个属性自动设置指定值的行为。
 *
 * 要使用 AttributesBehavior，就要配置 [[attributes]] 属性，它指明了需要更新的属性列表和
 * 触发这个更新操作对应的事件。然后再配置短数组的值为一个 PHP 匿名函数，
 * 该函数应该返回设置给当前属性的值。
 * 比如，
 *
 * ```php
 * use yii\behaviors\AttributesBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         [
 *             'class' => AttributesBehavior::className(),
 *             'attributes' => [
 *                 'attribute1' => [
 *                     ActiveRecord::EVENT_BEFORE_INSERT => new Expression('NOW()'),
 *                     ActiveRecord::EVENT_BEFORE_UPDATE => \Yii::$app->formatter->asDatetime('2017-07-13'),
 *                 ],
 *                 'attribute2' => [
 *                     ActiveRecord::EVENT_BEFORE_VALIDATE => [$this, 'storeAttributes'],
 *                     ActiveRecord::EVENT_AFTER_VALIDATE => [$this, 'restoreAttributes'],
 *                 ],
 *                 'attribute3' => [
 *                     ActiveRecord::EVENT_BEFORE_VALIDATE => $fn2 = [$this, 'getAttribute2'],
 *                     ActiveRecord::EVENT_AFTER_VALIDATE => $fn2,
 *                 ],
 *                 'attribute4' => [
 *                     ActiveRecord::EVENT_BEFORE_DELETE => function ($event, $attribute) {
 *                         static::disabled() || $event->isValid = false;
 *                     },
 *                 ],
 *             ],
 *         ],
 *     ];
 * }
 * ```
 *
 * 由于属性值会被这个行为自动设置，所以属性值不必用户输入也因此没有必要验证。
 * 因此，这些属性不应该出现在 [[\yii\base\Model::rules()|rules()]] 这个模型方法中。
 *
 * @author Luciano Baraglia <luciano.baraglia@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Bogdan Stepanenko <bscheshirwork@gmail.com>
 * @since 2.0.13
 */
class AttributesBehavior extends Behavior
{
    /**
     * @var array 指出将被自动更新的属性列表，而被更新的值通过短数组给出。
     * 数组的键就是更新于事件之上的 ActiveRecord 对象的属性，
     * 而数组的值则是对应事件的短数组。对这样的短数组而言：
     * 数组的键就是 ActiveRecord 事件，属性就是根据这些事件更新的，
     * 数组的值则是要设置给当前属性的值。数组的值可以是一个匿名函数，
     * 数组格式的回调方法（比如 `[$this, 'methodName']`），一个表示 DB 表达式的 [[\yii\db\Expression|Expression]] 对象
     * （比如 `new Expression('NOW()')`），标量，字符串或者一个任意的值。如果是前者，
     * 那么函数的返回值将设置给这个属性
     *
     * ```php
     * [
     *   'attribute1' => [
     *       ActiveRecord::EVENT_BEFORE_INSERT => new Expression('NOW()'),
     *       ActiveRecord::EVENT_BEFORE_UPDATE => \Yii::$app->formatter->asDatetime('2017-07-13'),
     *   ],
     *   'attribute2' => [
     *       ActiveRecord::EVENT_BEFORE_VALIDATE => [$this, 'storeAttributes'],
     *       ActiveRecord::EVENT_AFTER_VALIDATE => [$this, 'restoreAttributes'],
     *   ],
     *   'attribute3' => [
     *       ActiveRecord::EVENT_BEFORE_VALIDATE => $fn2 = [$this, 'getAttribute2'],
     *       ActiveRecord::EVENT_AFTER_VALIDATE => $fn2,
     *   ],
     *   'attribute4' => [
     *       ActiveRecord::EVENT_BEFORE_DELETE => function ($event, $attribute) {
     *           static::disabled() || $event->isValid = false;
     *       },
     *   ],
     * ]
     * ```
     */
    public $attributes = [];
    /**
     * @var array 用事件给出将被更新的属性的顺序列表。
     * 数组的键就是属性据此完成更新的 ActiveRecord 事件，
     * 而数组的值则是对应属性的顺序。
     * 不在这些数组里的属性将会在最后处理。
     * 如果 [[attributes]] 里的属性没有指明事件，那么这些属性会被忽略不被更新。
     *
     * ```php
     * [
     *     ActiveRecord::EVENT_BEFORE_VALIDATE => ['attribute1', 'attribute2'],
     *     ActiveRecord::EVENT_AFTER_VALIDATE => ['attribute2', 'attribute1'],
     * ]
     * ```
     */
    public $order = [];
    /**
     * @var bool 当 `$owner` 没被更新时是否跳过该行为。
     */
    public $skipUpdateOnClean = true;
    /**
     * @var bool 是否保留非空的值不更新。
     */
    public $preserveNonEmptyValues = false;


    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return array_fill_keys(
            array_reduce($this->attributes, function ($carry, $item) {
                return array_merge($carry, array_keys($item));
            }, []),
            'evaluateAttributes'
        );
    }

    /**
     * 解析属性的值并更新到当前属性上。
     * @param Event $event
     */
    public function evaluateAttributes($event)
    {
        if ($this->skipUpdateOnClean
            && $event->name === ActiveRecord::EVENT_BEFORE_UPDATE
            && empty($this->owner->dirtyAttributes)
        ) {
            return;
        }
        $attributes = array_keys(array_filter($this->attributes, function ($carry) use ($event) {
            return array_key_exists($event->name, $carry);
        }));
        if (!empty($this->order[$event->name])) {
            $attributes = array_merge(
                array_intersect((array) $this->order[$event->name], $attributes),
                array_diff($attributes, (array) $this->order[$event->name]));
        }
        foreach ($attributes as $attribute) {
            if ($this->preserveNonEmptyValues && !empty($this->owner->$attribute)) {
                continue;
            }
            $this->owner->$attribute = $this->getValue($attribute, $event);
        }
    }

    /**
     * 返回当前属性的值。
     * 该方法是由 [[evaluateAttributes()]] 内部调用的。
     * 它的返回值将会根据触发的事件设置到目标属性上。
     * @param string $attribute 目标属性名
     * @param Event $event 触发当前属性开始更新动作的事件
     * @return mixed 属性值
     */
    protected function getValue($attribute, $event)
    {
        if (!isset($this->attributes[$attribute][$event->name])) {
            return null;
        }
        $value = $this->attributes[$attribute][$event->name];
        if ($value instanceof Closure || (is_array($value) && is_callable($value))) {
            return $value($event, $attribute);
        }

        return $value;
    }
}
