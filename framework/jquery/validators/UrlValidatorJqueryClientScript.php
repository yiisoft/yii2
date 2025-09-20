<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yii\jquery\validators;

use yii\base\Model;
use yii\helpers\Json;
use yii\validators\client\ClientValidatorScriptInterface;
use yii\validators\PunycodeAsset;
use yii\validators\ValidationAsset;
use yii\validators\Validator;
use yii\web\JsExpression;
use yii\web\View;

/**
 * UrlValidatorJqueryClientScript provides client-side validation script generation for URL attributes.
 *
 * This class implements {@see ClientValidatorScriptInterface} to supply client-side validation options and register
 * the corresponding JavaScript code for URL validation in Yii2 forms using jQuery.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.2.0
 */
class UrlValidatorJqueryClientScript implements ClientValidatorScriptInterface
{
    public function getClientOptions(Validator $validator, Model $model, string $attribute): array
    {
        if (strpos($validator->pattern, '{schemes}') !== false) {
            $pattern = str_replace(
                '{schemes}',
                '(' . implode('|', $validator->validSchemes) . ')',
                $validator->pattern,
            );
        } else {
            $pattern = $validator->pattern;
        }

        $options = [
            'pattern' => new JsExpression($pattern),
            'message' => $validator->formatMessage(
                $validator->message,
                ['attribute' => $model->getAttributeLabel($attribute)],
            ),
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

    public function register(Validator $validator, Model $model, string $attribute, View $view): string
    {
        ValidationAsset::register($view);

        if ($validator->enableIDN) {
            PunycodeAsset::register($view);
        }

        $options = $this->getClientOptions($validator, $model, $attribute);

        return 'yii.validation.url(value, messages, ' . Json::htmlEncode($options) . ');';
    }
}
