<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jquery\validators\client;

use yii\jquery\ValidationAsset;
use yii\validators\client\ClientValidator;

/**
 * StringValidator composes client-side validation code from [[\yii\validators\StringValidator]].
 *
 * @see \yii\validators\StringValidator
 * @see ValidationAsset
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class StringValidator extends ClientValidator
{
    /**
     * {@inheritdoc}
     */
    public function build($validator, $model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($validator, $model, $attribute);
        return 'yii.validation.string(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

    /**
     * Returns the client-side validation options.
     * @param \yii\validators\StringValidator $validator the server-side validator.
     * @param \yii\base\Model $model the model being validated
     * @param string $attribute the attribute name being validated
     * @return array the client-side validation options
     */
    public function getClientOptions($validator, $model, $attribute)
    {
        $label = $model->getAttributeLabel($attribute);

        $options = [
            'message' => $validator->formatMessage($validator->message, [
                'attribute' => $label,
            ]),
        ];

        if ($validator->min !== null) {
            $options['min'] = $validator->min;
            $options['tooShort'] = $validator->formatMessage($validator->tooShort, [
                'attribute' => $label,
                'min' => $validator->min,
            ]);
        }
        if ($validator->max !== null) {
            $options['max'] = $validator->max;
            $options['tooLong'] = $validator->formatMessage($validator->tooLong, [
                'attribute' => $label,
                'max' => $validator->max,
            ]);
        }
        if ($validator->length !== null) {
            $options['is'] = $validator->length;
            $options['notEqual'] = $validator->formatMessage($validator->notEqual, [
                'attribute' => $label,
                'length' => $validator->length,
            ]);
        }
        if ($validator->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}