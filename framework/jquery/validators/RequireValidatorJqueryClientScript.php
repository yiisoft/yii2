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
 * RequireValidatorJqueryClientScript provides client-side validation script generation for required attributes.
 *
 * This class implements {@see ClientValidatorScriptInterface} to supply client-side validation options and register
 * the corresponding JavaScript code for required value validation in Yii2 forms using jQuery.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.2.0
 */
class RequireValidatorJqueryClientScript implements ClientValidatorScriptInterface
{
    public function getClientOptions(Validator $validator, Model $model, string $attribute): array
    {
        $options = [];

        if ($validator->requiredValue !== null) {
            $options['message'] = $validator->formatMessage(
                $validator->message,
                ['requiredValue' => $validator->requiredValue],
            );

            $options['requiredValue'] = $validator->requiredValue;
        } else {
            $options['message'] = $validator->message;
        }

        if ($validator->strict) {
            $options['strict'] = 1;
        }

        $options['message'] = $validator->formatMessage(
            $options['message'],
            ['attribute' => $model->getAttributeLabel($attribute)],
        );

        return $options;
    }

    public function register(Validator $validator, Model $model, string $attribute, View $view): string
    {
        ValidationAsset::register($view);

        $options = $this->getClientOptions($validator, $model, $attribute);

        return 'yii.validation.required(value, messages, ' . Json::htmlEncode($options) . ');';
    }
}
