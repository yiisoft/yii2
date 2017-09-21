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
 * RequiredValidator composes client-side validation code from [[\yii\validators\RequiredValidator]].
 *
 * @see \yii\validators\RequiredValidator
 * @see ValidationAsset
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class RequiredValidator extends ClientValidator
{
    /**
     * @inheritdoc
     */
    public function build($validator, $model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($validator, $model, $attribute);
        return 'yii.validation.required(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

    /**
     * Returns the client-side validation options.
     * @param \yii\validators\RequiredValidator $validator the server-side validator.
     * @param \yii\base\Model $model the model being validated
     * @param string $attribute the attribute name being validated
     * @return array the client-side validation options
     */
    public function getClientOptions($validator, $model, $attribute)
    {
        $options = [];
        if ($validator->requiredValue !== null) {
            $options['message'] = $validator->formatMessage($validator->message, [
                'requiredValue' => $validator->requiredValue,
            ]);
            $options['requiredValue'] = $validator->requiredValue;
        } else {
            $options['message'] = $validator->message;
        }
        if ($validator->strict) {
            $options['strict'] = 1;
        }

        $options['message'] = $validator->formatMessage($options['message'], [
            'attribute' => $model->getAttributeLabel($attribute),
        ]);

        return $options;
    }
}