<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\behaviors;

use Closure;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * AttributeBehavior automatically assigns a specified value to one or multiple attributes of an ActiveRecord
 * object when certain events happen.
 *
 * To use AttributeBehavior, configure the [[attributes]] property which should specify the list of attributes
 * that need to be updated and the corresponding events that should trigger the update. Then configure the
 * [[value]] property with a PHP callable whose return value will be used to assign to the current attribute(s).
 * For example,
 *
 * ```
 * use yii\behaviors\AttributeBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         [
 *             'class' => AttributeBehavior::class,
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
 * Because attribute values will be set automatically by this behavior, they are usually not user input and should therefore
 * not be validated, i.e. they should not appear in the [[\yii\base\Model::rules()|rules()]] method of the model.
 *
 * @author Luciano Baraglia <luciano.baraglia@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @template T of BaseActiveRecord
 * @extends Behavior<T>
 */
class AttributeBehavior extends Behavior
{
    /**
     * @var array list of attributes that are to be automatically filled with the value specified via [[value]].
     * The array keys are the ActiveRecord events upon which the attributes are to be updated,
     * and the array values are the corresponding attribute(s) to be updated. You can use a string to represent
     * a single attribute, or an array to represent a list of attributes. For example,
     *
     * ```
     * [
     *     ActiveRecord::EVENT_BEFORE_INSERT => ['attribute1', 'attribute2'],
     *     ActiveRecord::EVENT_BEFORE_UPDATE => 'attribute2',
     * ]
     * ```
     */
    public $attributes = [];
    /**
     * @var mixed the value that will be assigned to the current attributes. This can be an anonymous function,
     * callable in array format (e.g. `[$this, 'methodName']`), an [[\yii\db\Expression|Expression]] object representing a DB expression
     * (e.g. `new Expression('NOW()')`), scalar, string or an arbitrary value. If the former, the return value of the
     * function will be assigned to the attributes.
     * The signature of the function should be as follows,
     *
     * ```
     * function ($event)
     * {
     *     // return value will be assigned to the attribute
     * }
     * ```
     */
    public $value;
    /**
     * @var bool whether to skip this behavior when the `$owner` has not been
     * modified
     * @since 2.0.8
     */
    public $skipUpdateOnClean = true;
    /**
     * @var bool whether to preserve non-empty attribute values.
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
     * Evaluates the attribute value and assigns it to the current attributes.
     * @param Event $event
     */
    public function evaluateAttributes($event)
    {
        if (
            $this->skipUpdateOnClean
            && $event->name == ActiveRecord::EVENT_BEFORE_UPDATE
            && empty($this->owner->dirtyAttributes)
        ) {
            return;
        }

        if (!empty($this->attributes[$event->name])) {
            $attributes = (array) $this->attributes[$event->name];
            $value = $this->getValue($event);
            foreach ($attributes as $attribute) {
                // ignore attribute names which are not string (e.g. when set by TimestampBehavior::updatedAtAttribute)
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
     * Returns the value for the current attributes.
     * This method is called by [[evaluateAttributes()]]. Its return value will be assigned
     * to the attributes corresponding to the triggering event.
     * @param Event $event the event that triggers the current attribute updating.
     * @return mixed the attribute value
     */
    protected function getValue($event)
    {
        if ($this->value instanceof Closure || (is_array($this->value) && is_callable($this->value))) {
            return call_user_func($this->value, $event);
        }

        return $this->value;
    }
}
