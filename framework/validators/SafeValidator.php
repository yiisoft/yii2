<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * SafeValidator 充当一个虚拟校验器，它的主要目的是在批量赋值时标记指定的属性是安全的。
 *
 * 这个类的必要性在于 Yii 框架在批量赋值时可用来决定一个属性是否安全，
 * 即，如果一个用户提交表单，然后POST的数据被直接加载到一个模型中，这个校验器可以用于设置某个属性能否被安全复制。
 * 在许多情况下，这是必须的，但是，有时有一些属性是有"内置值"的，
 * 你并不想POST的数据覆盖这些内置值（比如类似数据库行的自增id），
 * Yii 在批量赋值时，假设所有不存在校验规则的属性都是不安全的，当然，大部分情况下也确实是这样的，但有时，也存在例外。
 * 这个时候，因为某个属性没有任何校验规则，你可以把这个类作为其校验规则。
 * 虽然它没有实现任何功能，但是，它可以让 Yii 觉得这个属性是安全可复制的。
 *
 * > Note: [[when]] property is not supported by SafeValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class SafeValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function validateAttributes($model, $attributes = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
    }
}
