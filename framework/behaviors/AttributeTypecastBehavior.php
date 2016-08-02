<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\behaviors;

use yii\base\Behavior;
use yii\base\InvalidParamException;
use yii\db\BaseActiveRecord;

/**
 * AttributeTypecastBehavior provides ability of automatic ActiveRecord attribute typecasting.
 * This behavior is very useful in case of usage schema-less databases like MongoDB or Redis.
 *
 * This behavior should be attached to [[\yii\db\BaseActiveRecord]] descendant.
 *
 * @property BaseActiveRecord $owner the owner of this behavior
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
     * @var array attribute typecast map in format: attributeName => type.
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
     *         return ($value instanceof \DateTime) ? $value->getTimestamp(): (int)$value;
     *     },
     * ]
     * ```
     */
    public $attributeTypes;
    /**
     * @var boolean whether to skip typecasting on `null` values.
     * If enabled any attribute, which value equals to `null` will not be processed.
     */
    public $skipOnNull = true;
    /**
     * @var boolean whether to perform typecasting after owner model validation.
     * Note that typecasting will be performed only if validation was successful, e.g.
     * owner model has no errors.
     */
    public $typecastAfterValidate = true;
    /**
     * @var boolean whether to perform typecasting before saving owner model (insert or update).
     * This option may be disabled in order to achieve better performance.
     * For example, in case of [[\yii\db\ActiveRecord]] usage, typecasting before save
     * will grant no benefit an thus can be disabled.
     */
    public $typecastBeforeSave = true;
    /**
     * @var boolean whether to perform typecasting after retrieving owner model data from
     * the database (after find or refresh).
     * This option may be disabled in order to achieve better performance.
     * For example, in case of [[\yii\db\ActiveRecord]] usage, typecasting after find
     * will grant no benefit in most cases an thus can be disabled.
     */
    public $typecastAfterFind = true;


    /**
     * Typecast owner attributes according to [[attributeTypes]].
     * @param null $attributeNames list of attribute names that should be type-casted.
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
                    throw new InvalidParamException("There is no type mapping for '{$attribute}'.");
                }
                $attributeTypes[$attribute] = $this->attributeTypes[$attribute];
            }
        }

        foreach ($attributeTypes as $attribute => $type) {
            $this->owner->{$attribute} = $this->typecastValue($this->owner->{$attribute}, $type);
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
        if ($this->skipOnNull && $value === null) {
            return $value;
        }

        if (is_scalar($type)) {
            if (is_object($value) && method_exists($value, '__toString')) {
                $value = $value->__toString();
            }

            switch ($type) {
                case self::TYPE_INTEGER:
                    return (int)$value;
                case self::TYPE_FLOAT:
                    return (float)$value;
                case self::TYPE_BOOLEAN:
                    return (boolean)$value;
                case self::TYPE_STRING:
                    return (string)$value;
                default:
                    throw new InvalidParamException("Unsupported type '{$type}'");
            }
        }

        return call_user_func($type,  $value);
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_AFTER_VALIDATE => 'afterValidate',
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            BaseActiveRecord::EVENT_AFTER_FIND => 'afterFind',
        ];
    }

    /**
     * Handles owner 'afterValidate' event, ensuring attribute typecasting.
     * @param \yii\base\Event $event event instance.
     */
    public function afterValidate($event)
    {
        if ($this->typecastAfterValidate && !$this->owner->hasErrors()) {
            $this->typecastAttributes();
        }
    }

    /**
     * Handles owner 'afterInsert' and 'afterUpdate' events, ensuring attribute typecasting.
     * @param \yii\base\Event $event event instance.
     */
    public function beforeSave($event)
    {
        if ($this->typecastBeforeSave) {
            $this->typecastAttributes();
        }
    }

    /**
     * Handles owner 'afterFind' event, ensuring attribute typecasting.
     * @param \yii\base\Event $event event instance.
     */
    public function afterFind($event)
    {
        if ($this->typecastAfterFind) {
            $this->typecastAttributes();
        }
    }
}