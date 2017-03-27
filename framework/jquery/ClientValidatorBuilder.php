<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jquery;

use Yii;
use yii\base\Object;

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
     * @var array client validator class map in format: [server-side-validator-class => client-side-validator].
     * Client side validator should be specified as an instance of [[\yii\validators\client\ClientValidator]] or
     * its DI compatible configuration.
     *
     * Class map respects validators inheritance, e.g. if you specify map for `ParentValidator` it will be used for
     * `ChildValidator` in case it extends `ParentValidator`. In case maps for both `ParentValidator` and `ChildValidator`
     * are specified the first value will take precedence.
     */
    public $clientValidatorMap = [
        \yii\validators\BooleanValidator::class => \yii\jquery\validators\client\BooleanValidator::class,
        \yii\validators\CompareValidator::class => \yii\jquery\validators\client\CompareValidator::class,
        \yii\validators\EmailValidator::class => \yii\jquery\validators\client\EmailValidator::class,
        \yii\validators\FilterValidator::class => \yii\jquery\validators\client\FilterValidator::class,
        \yii\validators\IpValidator::class => \yii\jquery\validators\client\IpValidator::class,
        \yii\validators\NumberValidator::class => \yii\jquery\validators\client\NumberValidator::class,
        \yii\validators\RangeValidator::class => \yii\jquery\validators\client\RangeValidator::class,
        \yii\validators\RegularExpressionValidator::class => \yii\jquery\validators\client\RegularExpressionValidator::class,
        \yii\validators\RequiredValidator::class => \yii\jquery\validators\client\RequiredValidator::class,
        \yii\validators\StringValidator::class => \yii\jquery\validators\client\StringValidator::class,
        \yii\validators\UrlValidator::class => \yii\jquery\validators\client\UrlValidator::class,
        \yii\validators\ImageValidator::class => \yii\jquery\validators\client\ImageValidator::class,
        \yii\validators\FileValidator::class => \yii\jquery\validators\client\FileValidator::class,
        \yii\captcha\CaptchaValidator::class => \yii\captcha\CaptchaClientValidator::class,
    ];


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
        foreach ($this->clientValidatorMap as $serverSideValidatorClass => $clientSideValidator) {
            if ($validator instanceof $serverSideValidatorClass) {
                /* @var $clientValidator \yii\validators\client\ClientValidator */
                $clientValidator = Yii::createObject($clientSideValidator);
                return $clientValidator->build($validator, $model, $attribute, $view);
            }
        }
        return null;
    }
}