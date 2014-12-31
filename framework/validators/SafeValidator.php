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
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class SafeValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
    }
}
