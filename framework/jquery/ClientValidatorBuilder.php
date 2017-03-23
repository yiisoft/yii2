<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jquery;

use yii\base\Object;
use yii\helpers\Json;
use yii\validators\BooleanValidator;
use yii\validators\CompareValidator;
use yii\validators\EmailValidator;
use yii\validators\FilterValidator;
use yii\validators\IpValidator;
use yii\validators\NumberValidator;
use yii\validators\RangeValidator;
use yii\validators\RegularExpressionValidator;
use yii\validators\RequiredValidator;
use yii\validators\StringValidator;
use yii\validators\UrlValidator;

/**
 * ClientValidatorBuilder performs composition of the JavaScript code used for model attribute client validation.
 * It processes instances of [[\yii\validators\Validator]] creating a corresponding JavaScript code based on `yii.validation`
 * jQuery component.
 *
 * Note: this class does not take into account result of [[\yii\validators\Validator::clientValidateAttribute()]] method
 * ignoring client-side validation code possibly provided by it.
 *
 * @see ActiveForm
 * @see \yii\validators\Validator
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1
 */
class ClientValidatorBuilder extends Object
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
    public function build($validator, $model, $attribute, $view)
    {
        if ($validator instanceof BooleanValidator) {
            ValidationAsset::register($view);
            $options = $validator->getClientOptions($model, $attribute);
            return 'yii.validation.boolean(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
        }

        if ($validator instanceof CompareValidator) {
            ValidationAsset::register($view);
            $options = $validator->getClientOptions($model, $attribute);
            return 'yii.validation.compare(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
        }

        if ($validator instanceof EmailValidator) {
            ValidationAsset::register($view);
            if ($validator->enableIDN) {
                PunycodeAsset::register($view);
            }
            $options = $validator->getClientOptions($model, $attribute);
            return 'yii.validation.email(value, messages, ' . Json::htmlEncode($options) . ');';
        }

        if ($validator instanceof FilterValidator) {
            if ($validator->filter !== 'trim') {
                return null;
            }
            ValidationAsset::register($view);
            $options = $validator->getClientOptions($model, $attribute);
            return 'value = yii.validation.trim($form, attribute, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
        }

        if ($validator instanceof IpValidator) {
            ValidationAsset::register($view);
            $options = $validator->getClientOptions($model, $attribute);
            return 'yii.validation.ip(value, messages, ' . Json::htmlEncode($options) . ');';
        }

        if ($validator instanceof NumberValidator) {
            ValidationAsset::register($view);
            $options = $validator->getClientOptions($model, $attribute);
            return 'yii.validation.number(value, messages, ' . Json::htmlEncode($options) . ');';
        }

        if ($validator instanceof RangeValidator) {
            if ($validator->range instanceof \Closure) {
                $validator->range = call_user_func($validator->range, $model, $attribute);
            }
            ValidationAsset::register($view);
            $options = $validator->getClientOptions($model, $attribute);
            return 'yii.validation.range(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
        }

        if ($validator instanceof RegularExpressionValidator) {
            ValidationAsset::register($view);
            $options = $validator->getClientOptions($model, $attribute);
            return 'yii.validation.regularExpression(value, messages, ' . Json::htmlEncode($options) . ');';
        }

        if ($validator instanceof RequiredValidator) {
            ValidationAsset::register($view);
            $options = $validator->getClientOptions($model, $attribute);
            return 'yii.validation.required(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
        }

        if ($validator instanceof StringValidator) {
            ValidationAsset::register($view);
            $options = $validator->getClientOptions($model, $attribute);
            return 'yii.validation.string(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
        }

        if ($validator instanceof UrlValidator) {
            ValidationAsset::register($view);
            if ($validator->enableIDN) {
                PunycodeAsset::register($view);
            }
            $options = $validator->getClientOptions($model, $attribute);
            return 'yii.validation.url(value, messages, ' . Json::htmlEncode($options) . ');';
        }

        return null;
    }
}