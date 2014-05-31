<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * DefaultValueValidator sets the attribute to be the specified default value.
 *
 * DefaultValueValidator is not really a validator. It is provided mainly to allow
 * specifying attribute default values when they are empty.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DefaultValueValidator extends Validator
{
    /**
     * @var mixed a PHP callable returning the default value or the default value to be set to the specified attributes.
     * The function signature must be as follows,
     *
     * ~~~
     * function foo($object, $attribute) {
     *     // compute value
     *     return $value;
     * }
     * ~~~
     */
    public $value;
    /**
     * @var boolean this property is overwritten to be false so that this validator will
     * be applied when the value being validated is empty.
     */
    public $skipOnEmpty = false;

    /**
     * @inheritdoc
     */
    public function validateAttribute($object, $attribute)
    {
        if ($this->isEmpty($object->$attribute)) {
            if ($this->value instanceof \Closure) {
                $object->$attribute = call_user_func($this->value, $object, $attribute);
            } else {
                $object->$attribute = $this->value;
            }
        }
    }
}
