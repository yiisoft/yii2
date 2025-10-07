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
use yii\validators\StringValidator;
use yii\validators\ValidationAsset;
use yii\validators\Validator;
use yii\web\View;

/**
 * StringValidatorJqueryClientScript provides client-side validation script generation for string attributes.
 *
 * This class implements {@see ClientValidatorScriptInterface} to supply client-side validation options and register the
 * corresponding JavaScript code for string validation in Yii2 forms using jQuery.
 *
 * @template T of StringValidator
 * @implements ClientValidatorScriptInterface<T>
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.2.0
 */
class StringValidatorJqueryClientScript implements ClientValidatorScriptInterface
{
    public function getClientOptions(Validator $validator, Model $model, string $attribute): array
    {
        $label = $model->getAttributeLabel($attribute);

        $options = [
            'message' => $validator->getFormattedClientMessage(
                $validator->message,
                ['attribute' => $label],
            ),
        ];

        if ($validator->min !== null) {
            $options['min'] = $validator->min;

            $options['tooShort'] = $validator->getFormattedClientMessage(
                $validator->tooShort,
                [
                    'attribute' => $label,
                    'min' => $validator->min,
                ],
            );
        }

        if ($validator->max !== null) {
            $options['max'] = $validator->max;

            $options['tooLong'] = $validator->getFormattedClientMessage(
                $validator->tooLong,
                [
                    'attribute' => $label,
                    'max' => $validator->max,
                ],
            );
        }

        if ($validator->length !== null) {
            $options['is'] = $validator->length;

            $options['notEqual'] = $validator->getFormattedClientMessage(
                $validator->notEqual,
                [
                    'attribute' => $label,
                    'length' => $validator->length,
                ],
            );
        }

        if ($validator->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }

    public function register(Validator $validator, Model $model, string $attribute, View $view): string
    {
        ValidationAsset::register($view);

        $options = $this->getClientOptions($validator, $model, $attribute);

        return 'yii.validation.string(value, messages, ' . Json::htmlEncode($options) . ');';
    }
}
