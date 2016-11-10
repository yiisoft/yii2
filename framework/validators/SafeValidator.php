<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * SafeValidator serves as a dummy validator whose main purpose is to mark the attributes to be safe for massive assignment.
 *
 * This class is required because of the way in which Yii determines whether a property is safe for massive assignment, that is,
 * when a user submits form data to be loaded into a model directly from the POST data, is it ok for a property to be copied.
 * In many cases, this is required but because sometimes properties are internal and you do not want the POST data to be able to
 * override these internal values (especially things like database row ids), Yii assumes all values are unsafe for massive assignment
 * unless a validation rule exists for the property, which in most cases it will. Sometimes, however, an item is safe for massive assigment but
 * does not have a validation rule associated with it - for instance, due to no validation being performed, in which case, you use this class
 * as a validation rule for that property. Although it has no functionality, it allows Yii to determine that the property is safe to copy.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class SafeValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function validateAttributes($model, $attributes = null)
    {
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
    }
}
