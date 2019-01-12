<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * SafeValidator serves as a dummy validator whose main purpose is to mark the attributes to be safe for massive assignment.
 * SafeValidator 充当一个虚拟校验器，它的主要目的是在批量赋值时标记指定的属性是安全的。
 *
 * This class is required because of the way in which Yii determines whether a property is safe for massive assignment, that is,
 * when a user submits form data to be loaded into a model directly from the POST data, is it ok for a property to be copied.
 * In many cases, this is required but because sometimes properties are internal and you do not want the POST data to be able to
 * override these internal values (especially things like database row ids), Yii assumes all values are unsafe for massive assignment
 * unless a validation rule exists for the property, which in most cases it will. Sometimes, however, an item is safe for massive assigment but
 * does not have a validation rule associated with it - for instance, due to no validation being performed, in which case, you use this class
 * as a validation rule for that property. Although it has no functionality, it allows Yii to determine that the property is safe to copy.
 * 这个类的必要性在于 Yii 框架在批量赋值时可用来决定一个属性是否安全，即，如果一个用户提交表单，然后POST的数据被直接加载到一个模型中，这个校验器可以用于设置某个属性能否被安全复制。
 * 在许多情况下，这是必须的，但是，有时有一些属性是有"内置值"的，你并不想POST的数据覆盖这些内置值（比如类似数据库行的自增id），Yii 在批量赋值时，假设所有不存在校验规则的属性都是不安全的，
 * 当然，大部分情况下也确实是这样的，但有时，也存在例外。这个时候，因为某个属性没有任何校验规则，你可以把这个类作为其校验规则。虽然它没有实现任何功能，但是，它可以让 Yii 觉得这个属性是安全可复制的。
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
