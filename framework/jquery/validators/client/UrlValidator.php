<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jquery\validators\client;

use yii\helpers\Json;
use yii\jquery\PunycodeAsset;
use yii\jquery\ValidationAsset;
use yii\validators\client\ClientValidator;
use yii\web\JsExpression;

/**
 * UrlValidator composes client-side validation code from [[\yii\validators\UrlValidator]].
 *
 * @see \yii\validators\UrlValidator
 * @see ValidationAsset
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class UrlValidator extends ClientValidator
{
    /**
     * {@inheritdoc}
     */
    public function build($validator, $model, $attribute, $view)
    {
        /* @var $validator \yii\validators\UrlValidator */
        ValidationAsset::register($view);
        if ($validator->enableIDN) {
            PunycodeAsset::register($view);
        }
        $options = $this->getClientOptions($validator, $model, $attribute);
        return 'yii.validation.url(value, messages, ' . Json::htmlEncode($options) . ');';
    }

    /**
     * Returns the client-side validation options.
     * @param \yii\validators\UrlValidator $validator the server-side validator.
     * @param \yii\base\Model $model the model being validated
     * @param string $attribute the attribute name being validated
     * @return array the client-side validation options
     */
    public function getClientOptions($validator, $model, $attribute)
    {
        if (strpos($validator->pattern, '{schemes}') !== false) {
            $pattern = str_replace('{schemes}', '(' . implode('|', $validator->validSchemes) . ')', $validator->pattern);
        } else {
            $pattern = $validator->pattern;
        }

        $options = [
            'pattern' => new JsExpression($pattern),
            'message' => $validator->formatMessage($validator->message, [
                'attribute' => $model->getAttributeLabel($attribute),
            ]),
            'enableIDN' => (bool) $validator->enableIDN,
        ];
        if ($validator->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }
        if ($validator->defaultScheme !== null) {
            $options['defaultScheme'] = $validator->defaultScheme;
        }

        return $options;
    }
}