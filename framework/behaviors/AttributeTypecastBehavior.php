<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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
 * AttributeTypecastBehavior provides an ability of automatic model attribute typecasting.
 * This behavior is very useful in case of usage of ActiveRecord for the schema-less databases like MongoDB or Redis.
 * It may also come in handy for regular [[\yii\db\ActiveRecord]] or even [[\yii\base\Model]], allowing to maintain
 * strict attribute types after model validation.
 *
 * This behavior should be attached to [[\yii\base\Model]] or [[\yii\db\BaseActiveRecord]] descendant.
 *
 * You should specify exact attribute types via [[attributeTypes]].
 *
 * For example:
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
 *                 'class' => AttributeTypecastBehavior::class,
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
 * Tip: you may left [[attributeTypes]] blank - in this case its value will be detected
 * automatically based on owner validation rules.
 * Following example will automatically create same [[attributeTypes]] value as it was configured at the above one:
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
 *                 'class' => AttributeTypecastBehavior::class,
 *                 // 'attributeTypes' will be composed automatically according to `rules()`
 *             ],
 *         ];
 *     }
 *
 *     // ...
 * }
 * ```
 *
 * This behavior allows automatic attribute typecasting at following cases:
 *
 * - after successful model validation
 * - before model save (insert or update)
 * - after model find (found by query or refreshed)
 *
 * You may control automatic typecasting for particular case using fields [[typecastAfterValidate]],
 * [[typecastBeforeSave]] and [[typecastAfterFind]].
 * By default typecasting will be performed only after model validation.
 *
 * Note: you can manually trigger attribute typecasting anytime invoking [[typecastAttributes()]] method:
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
     * @var Model|BaseActiveRecord the owner of this behavior.
     */
    public $owner;
    /**
     * @var array|null attribute typecast map in format: attributeName => type.
     * Type can be set via PHP callable, which accept raw value as an argument and should return
     * typecast result.
     * For example:
     *
     * ```php
     * [
     *     'amount' => 'integer',
     *     'price' => 'float',
     *     'is_active' => 'boolean',
     *     'date' => function ($value) {
     *         return ($value instanceof \DateTime) ? $value->getTimestamp(): (int) $value;
     *     },
     * ]
     * ```
     *
     * If not set, attribute type map will be composed automatically from the owner validation rules.
     */
    public $attributeTypes;
    /**
     * @var bool whether to skip typecasting of `null` values.
     * If enabled attribute value which equals to `null` will not be type-casted (e.g. `null` remains `null`),
     * otherwise it will be converted according to the type configured at [[attributeTypes]].
     */
    public $skipOnNull = true;
    /**
     * @var bool whether to perform typecasting after owner model validation.
     * Note that typecasting will be performed only if validation was successful, e.g.
     * owner model has no errors.
     * Note that changing this option value will have no effect after this behavior has been attached to the model.
     */
    public $typecastAfterValidate = true;
    /**
     * @var bool whether to perform typecasting before saving owner model (insert or update).
     * This option may be disabled in order to achieve better performance.
     * For example, in case of [[\yii\db\ActiveRecord]] usage, typecasting before save
     * will grant no benefit an thus can be disabled.
     * Note that changing this option value will have no effect after this behavior has been attached to the model.
     */
    public $typecastBeforeSave = false;
    /**
     * @var bool whether to perform typecasting after saving owner model (insert or update).
     * This option may be disabled in order to achieve better performance.
     * For example, in case of [[\yii\db\ActiveRecord]] usage, typecasting after save
     * will grant no benefit an thus can be disabled.
     * Note that changing this option value will have no effect after this behavior has been attached to the model.
     * @since 2.0.14
     */
    public $typecastAfterSave = false;
    /**
     * @var bool whether to perform typecasting after retrieving owner model data from
     * the database (after find or refresh).
     * This option may be disabled in order to achieve better performance.
     * For example, in case of [[\yii\db\ActiveRecord]] usage, typecasting after find
     * will grant no benefit in most cases an thus can be disabled.
     * Note that changing this option value will have no effect after this behavior has been attached to the model.
     */
    public $typecastAfterFind = false;

    /**
     * @var array internal static cache for auto detected [[attributeTypes]] values
     * in format: ownerClassName => attributeTypes
     */
    private static $autoDetectedAttributeTypes = [];


    /**
     * Clears internal static cache of auto detected [[attributeTypes]] values
     * over all affected owner classes.
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
     * Typecast owner attributes according to [[attributeTypes]].
     * @param array|null $attributeNames list of attribute names that should be type-casted.
     * If this parameter is empty, it means any attribute listed in the [[attributeTypes]]
     * should be type-casted.
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
     * Casts the given value to the specified type.
     * @param mixed $value value to be type-casted.
     * @param string|callable $type type name or typecast callable.
     * @return mixed typecast result.
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
     * Composes default value for [[attributeTypes]] from the owner validation rules.
     * @return array attribute type map.
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
                $attributeTypes += array_fill_keys($validator->getAttributeNames(), $type);
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
     * Handles owner 'afterValidate' event, ensuring attribute typecasting.
     * @param \yii\base\Event $event event instance.
     */
    public function afterValidate($event)
    {
        if (!$this->owner->hasErrors()) {
            $this->typecastAttributes();
        }
    }

    /**
     * Handles owner 'beforeInsert' and 'beforeUpdate' events, ensuring attribute typecasting.
     * @param \yii\base\Event $event event instance.
     */
    public function beforeSave($event)
    {
        $this->typecastAttributes();
    }

    /**
     * Handles owner 'afterInsert' and 'afterUpdate' events, ensuring attribute typecasting.
     * @param \yii\base\Event $event event instance.
     * @since 2.0.14
     */
    public function afterSave($event)
    {
        $this->typecastAttributes();
    }

    /**
     * Handles owner 'afterFind' event, ensuring attribute typecasting.
     * @param \yii\base\Event $event event instance.
     */
    public function afterFind($event)
    {
        $this->typecastAttributes();

        $this->resetOldAttributes();
    }

    /**
     * Resets the old values of the named attributes.
     */
    protected function resetOldAttributes()
    {
        if ($this->attributeTypes === null) {
            return;
        }

        $attributes = array_keys($this->attributeTypes);

        foreach ($attributes as $attribute) {
            if ($this->owner->canSetOldAttribute($attribute)) {
                $this->owner->setOldAttribute($attribute, $this->owner->{$attribute});
            }
        }
    }
}
