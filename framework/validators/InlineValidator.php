<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * InlineValidator represents a validator which is defined as a method in the object being validated.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InlineValidator extends Validator
{
    /**
     * @var string|callable an anonymous function or the name of a model class method that will be
     * called to perform the actual validation. The signature of the method should be like the following:
     *
     * ```
     * function (string $attribute, mixed $params, InlineValidator $validator, mixed $current): bool {
     * }
     * ```
     *
     * - `$attribute` is the name of the attribute to be validated
     * - `$params` contains the value of [[params]] that you specify when declaring the inline validation rule
     * - `$validator` is a reference to related [[InlineValidator]] object. This parameter is available since version 2.0.11
     * - `$current` is the attribute value. This parameter is available since version 2.0.36
     */
    public $method;
    /**
     * @var mixed additional parameters that are passed to the validation method
     */
    public $params;
    /**
     * @var string|\Closure an anonymous function or the name of a model class method that returns the client validation code.
     * The signature of the method should be like the following:
     *
     * ```
     * function (string $attribute, mixed $params, InlineValidator $validator, mixed $current, View $view): string
     * {
     *     // $view->registerJs('JS validation function');
     *     // or \app\assets\ValidationAsset::register($view);
     *     return "calling JS validation function";
     * }
     * ```
     *
     * Please refer to [[clientValidateAttribute()]] and [guide](guide:input-validation#client-side-validation) for details on how
     * to return client validation code.
     */
    public $clientValidate;
    /**
     * @var mixed the value of attribute being currently validated.
     * @since 2.0.36
     */
    public $current;


    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $method = $this->method;
        if (is_string($method)) {
            $method = [$model, $method];
        } elseif ($method instanceof \Closure) {
            $method = $method->bindTo($model);
        }

        $current = $this->current;
        if ($current === null) {
            $current = $model->$attribute;
        }
        $method($attribute, $this->params, $this, $current);
    }

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        if ($this->clientValidate !== null) {
            $method = $this->clientValidate;
            if (is_string($method)) {
                $method = [$model, $method];
            } elseif ($method instanceof \Closure) {
                $method = $method->bindTo($model);
            }
            $current = $this->current;
            if ($current === null) {
                $current = $model->$attribute;
            }
            return $method($attribute, $this->params, $this, $current, $view);
        }

        return null;
    }
}
