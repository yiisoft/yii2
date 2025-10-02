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
use yii\validators\ValidationAsset;
use yii\validators\Validator;
use yii\web\View;

/**
 * BooleanValidatorJqueryClientScript provides client-side validation script generation for boolean attributes.
 *
 * This class implements {@see ClientValidatorScriptInterface} to supply client-side validation options and register
 * the corresponding JavaScript code for boolean validation in Yii2 forms using jQuery.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.2.0
 */
class BooleanValidatorJqueryClientScript implements ClientValidatorScriptInterface
{
    public function getClientOptions(Validator $validator, Model $model, string $attribute): array
    {
        $options = [
            'trueValue' => $validator->trueValue,
            'falseValue' => $validator->falseValue,
            'message' => $validator->formatMessage(
                $validator->message,
                [
                    'attribute' => $model->getAttributeLabel($attribute),
                    'true' => $validator->trueValue === true ? 'true' : $validator->trueValue,
                    'false' => $validator->falseValue === false ? 'false' : $validator->falseValue,
                ],
            ),
        ];

        if ($validator->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        if ($validator->strict) {
            $options['strict'] = 1;
        }

        return $options;
    }

    public function register(Validator $validator, Model $model, string $attribute, View $view): string
    {
        ValidationAsset::register($view);

        $options = $this->getClientOptions($validator, $model, $attribute);

        return 'yii.validation.boolean(value, messages, ' . Json::htmlEncode($options) . ');';
    }
}
