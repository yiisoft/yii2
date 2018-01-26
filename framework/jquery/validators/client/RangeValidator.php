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
 * RangeValidator composes client-side validation code from [[\yii\validators\RangeValidator]].
 *
 * @see \yii\validators\RangeValidator
 * @see ValidationAsset
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class RangeValidator extends ClientValidator
{
    /**
     * {@inheritdoc}
     */
    public function build($validator, $model, $attribute, $view)
    {
        /* @var $validator \yii\validators\RangeValidator */
        if ($validator->range instanceof \Closure) {
            $validator->range = call_user_func($validator->range, $model, $attribute);
        }
        ValidationAsset::register($view);
        $options = $validator->getClientOptions($model, $attribute);
        return 'yii.validation.range(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

    /**
     * Returns the client-side validation options.
     * @param \yii\validators\RangeValidator $validator the server-side validator.
     * @param \yii\base\Model $model the model being validated
     * @param string $attribute the attribute name being validated
     * @return array the client-side validation options
     */
    public function getClientOptions($validator, $model, $attribute)
    {
        $range = [];
        foreach ($validator->range as $value) {
            $range[] = (string) $value;
        }
        $options = [
            'range' => $range,
            'not' => $validator->not,
            'message' => $validator->formatMessage($validator->message, [
                'attribute' => $model->getAttributeLabel($attribute),
            ]),
        ];
        if ($validator->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }
        if ($validator->allowArray) {
            $options['allowArray'] = 1;
        }

        return $options;
    }
}