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
 * AttributesBehavior automatically assigns values specified to one or multiple attributes of an ActiveRecord
 * object when certain events happen.
 *
 * To use AttributesBehavior, configure the [[attributes]] property which should specify the list of attributes
 * that need to be updated and the corresponding events that should trigger the update. Then configure the
 * value of enclosed arrays with a PHP callable whose return value will be used to assign to the current attribute.
 * For example,
 *
 * ```php
 * use yii\behaviors\AttributesBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         [
 *             '__class' => AttributesBehavior::class,
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
 * Because attribute values will be set automatically by this behavior, they are usually not user input and should therefore
 * not be validated, i.e. they should not appear in the [[\yii\base\Model::rules()|rules()]] method of the model.
 *
 * @author Luciano Baraglia <luciano.baraglia@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Bogdan Stepanenko <bscheshirwork@gmail.com>
 * @since 2.0.13
 */
class AttributesBehavior extends Behavior
{
    /**
     * @var array list of attributes that are to be automatically filled with the values specified via enclosed arrays.
     * The array keys are the ActiveRecord attributes upon which the events are to be updated,
     * and the array values are the array of corresponding events(s). For this enclosed array:
     * the array keys are the ActiveRecord events upon which the attributes are to be updated,
     * and the array values are the value that will be assigned to the current attributes. This can be an anonymous function,
     * callable in array format (e.g. `[$this, 'methodName']`), an [[\yii\db\Expression|Expression]] object representing a DB expression
     * (e.g. `new Expression('NOW()')`), scalar, string or an arbitrary value. If the former, the return value of the
     * function will be assigned to the attributes.
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
     * @var array list of order of attributes that are to be automatically filled with the event.
     * The array keys are the ActiveRecord events upon which the attributes are to be updated,
     * and the array values are represent the order corresponding attributes.
     * The rest of the attributes are processed at the end.
     * If the [[attributes]] for this attribute do not specify this event, it is ignored
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
     * @var bool whether to skip this behavior when the `$owner` has not been modified
     */
    public $skipUpdateOnClean = true;
    /**
     * @var bool whether to preserve non-empty attribute values.
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
     * Evaluates the attributes values and assigns it to the current attributes.
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
     * Returns the value for the current attributes.
     * This method is called by [[evaluateAttributes()]]. Its return value will be assigned
     * to the target attribute corresponding to the triggering event.
     * @param string $attribute target attribute name
     * @param Event $event the event that triggers the current attribute updating.
     * @return mixed the attribute value
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
