<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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
     * @var mixed the default value or an anonymous function that returns the default value which will
     * be assigned to the attributes being validated if they are empty. The signature of the anonymous function
     * should be as follows,
     *
     * ```
     * function($model, $attribute) {
     *     // compute value
     *     return $value;
     * }
     * ```
     */
    public $value;
    /**
     * @var bool this property is overwritten to be false so that this validator will
     * be applied when the value being validated is empty.
     */
    public $skipOnEmpty = false;


    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        if ($this->isEmpty($model->$attribute)) {
            if ($this->value instanceof \Closure) {
                $model->$attribute = call_user_func($this->value, $model, $attribute);
            } else {
                $model->$attribute = $this->value;
            }
        }
    }
}
