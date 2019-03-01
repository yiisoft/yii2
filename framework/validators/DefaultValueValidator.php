<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * DefaultValueValidator 将属性设置为指定的默认值。
 *
 * DefaultValueValidator 严格意义上来说，不是一个校验器。
 * 它主要用于当一个属性为空时，给其提供一个默认值。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DefaultValueValidator extends Validator
{
    /**
     * @var mixed 默认值或者返还一个默认值的匿名函数，
     * 这个默认值将会在被校验的属性为空时，默认赋值给它。
     * 默认的匿名函数声明如下：
     *
     * ```php
     * function($model, $attribute) {
     *     // compute value
     *     return $value;
     * }
     * ```
     */
    public $value;
    /**
     * @var bool 这个属性被重写为 false ，
     * 这样当被校验的值为空时也会执行这个校验器。
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
