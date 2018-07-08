<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators\client;

use yii\base\BaseObject;

/**
 * ClientValidator composes client-side validation code from [[\yii\validators\Validator]] instance.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 3.0.0
 */
abstract class ClientValidator extends BaseObject
{
    /**
     * Builds the JavaScript needed for performing client-side validation for given validator.
     * @param \yii\validators\Validator $validator validator to be built.
     * @param \yii\base\Model $model the data model being validated.
     * @param string $attribute the name of the attribute to be validated.
     * @param \yii\web\View $view the view object that is going to be used to render views or view files
     * containing a model form with validator applied.
     * @return string|null client-side validation JavaScript code, `null` - if given validator is not supported.
     */
    abstract public function build($validator, $model, $attribute, $view);
}