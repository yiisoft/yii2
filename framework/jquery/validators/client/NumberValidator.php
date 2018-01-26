<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jquery\validators\client;

use yii\helpers\Json;
use yii\jquery\ValidationAsset;
use yii\validators\client\ClientValidator;
use yii\web\JsExpression;

/**
 * NumberValidator composes client-side validation code from [[\yii\validators\NumberValidator]].
 *
 * @see \yii\validators\NumberValidator
 * @see ValidationAsset
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class NumberValidator extends ClientValidator
{
    /**
     * {@inheritdoc}
     */
    public function build($validator, $model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($validator, $model, $attribute);
        return 'yii.validation.number(value, messages, ' . Json::htmlEncode($options) . ');';
    }

    /**
     * Returns the client-side validation options.
     * @param \yii\validators\NumberValidator $validator the server-side validator.
     * @param \yii\base\Model $model the model being validated
     * @param string $attribute the attribute name being validated
     * @return array the client-side validation options
     */
    public function getClientOptions($validator, $model, $attribute)
    {
        $label = $model->getAttributeLabel($attribute);

        $options = [
            'pattern' => new JsExpression($validator->integerOnly ? $validator->integerPattern : $validator->numberPattern),
            'message' => $validator->formatMessage($validator->message, [
                'attribute' => $label,
            ]),
        ];

        if ($validator->min !== null) {
            // ensure numeric value to make javascript comparison equal to PHP comparison
            // https://github.com/yiisoft/yii2/issues/3118
            $options['min'] = is_string($validator->min) ? (float) $validator->min : $validator->min;
            $options['tooSmall'] = $validator->formatMessage($validator->tooSmall, [
                'attribute' => $label,
                'min' => $validator->min,
            ]);
        }
        if ($validator->max !== null) {
            // ensure numeric value to make javascript comparison equal to PHP comparison
            // https://github.com/yiisoft/yii2/issues/3118
            $options['max'] = is_string($validator->max) ? (float) $validator->max : $validator->max;
            $options['tooBig'] = $validator->formatMessage($validator->tooBig, [
                'attribute' => $label,
                'max' => $validator->max,
            ]);
        }
        if ($validator->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}