<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yii\jquery\validators;

use yii\base\Model;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\validators\client\ClientValidatorScriptInterface;
use yii\validators\ValidationAsset;
use yii\validators\Validator;
use yii\web\View;

/**
 * CompareValidatorJqueryClientScript provides client-side validation script generation for attribute comparison.
 *
 * This class implements {@see ClientValidatorScriptInterface} to supply client-side validation options and register
 * the corresponding JavaScript code for comparison validation in Yii2 forms using jQuery.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.2.0
 */
class CompareValidatorJqueryClientScript implements ClientValidatorScriptInterface
{
    public function getClientOptions(Validator $validator, Model $model, string $attribute): array
    {
        $options = [
            'operator' => $validator->operator,
            'type' => $validator->type,
        ];

        if ($validator->compareValue !== null) {
            $options['compareValue'] = $validator->compareValue;
            $compareLabel = $compareValue = $compareValueOrAttribute = $validator->compareValue;
        } else {
            $compareAttribute = $validator->compareAttribute === null ? $attribute . '_repeat' : $validator->compareAttribute;

            $compareValue = $model->getAttributeLabel($compareAttribute);
            $options['compareAttribute'] = Html::getInputId($model, $compareAttribute);
            $options['compareAttributeName'] = Html::getInputName($model, $compareAttribute);
            $compareLabel = $compareValueOrAttribute = $model->getAttributeLabel($compareAttribute);
        }

        if ($validator->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        $options['message'] = $validator->formatMessage(
            $validator->message,
            [
                'attribute' => $model->getAttributeLabel($attribute),
                'compareAttribute' => $compareLabel,
                'compareValue' => $compareValue,
                'compareValueOrAttribute' => $compareValueOrAttribute,
            ],
        );

        return $options;
    }

    public function register(Validator $validator, Model $model, string $attribute, View $view): string
    {
        if ($validator->compareValue != null && $validator->compareValue instanceof \Closure) {
            $validator->compareValue = call_user_func($validator->compareValue);
        }

        ValidationAsset::register($view);

        $options = $this->getClientOptions($validator, $model, $attribute);

        return 'yii.validation.compare(value, messages, ' . Json::htmlEncode($options) . ', $form);';
    }
}
