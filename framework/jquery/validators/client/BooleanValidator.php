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
 * BooleanValidator composes client-side validation code from [[\yii\validators\BooleanValidator]].
 *
 * @see \yii\validators\BooleanValidator
 * @see ValidationAsset
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class BooleanValidator extends ClientValidator
{
    /**
     * @inheritdoc
     */
    public function build($validator, $model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($validator, $model, $attribute);
        return 'yii.validation.boolean(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

    /**
     * Returns the client-side validation options.
     * @param \yii\validators\BooleanValidator $validator the server-side validator.
     * @param \yii\base\Model $model the model being validated
     * @param string $attribute the attribute name being validated
     * @return array the client-side validation options
     */
    public function getClientOptions($validator, $model, $attribute)
    {
        $options = [
            'trueValue' => $validator->trueValue,
            'falseValue' => $validator->falseValue,
            'message' => $validator->formatMessage($validator->message, [
                'attribute' => $model->getAttributeLabel($attribute),
                'true' => $validator->trueValue === true ? 'true' : $validator->trueValue,
                'false' => $validator->falseValue === false ? 'false' : $validator->falseValue,
            ]),
        ];
        if ($validator->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }
        if ($validator->strict) {
            $options['strict'] = 1;
        }

        return $options;
    }
}