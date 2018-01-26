<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jquery\validators\client;

use yii\helpers\Json;
use yii\jquery\PunycodeAsset;
use yii\jquery\ValidationAsset;
use yii\validators\client\ClientValidator;
use yii\web\JsExpression;

/**
 * EmailValidator composes client-side validation code from [[\yii\validators\EmailValidator]].
 *
 * @see \yii\validators\EmailValidator
 * @see ValidationAsset
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class EmailValidator extends ClientValidator
{
    /**
     * {@inheritdoc}
     */
    public function build($validator, $model, $attribute, $view)
    {
        /* @var $validator \yii\validators\EmailValidator */
        ValidationAsset::register($view);
        if ($validator->enableIDN) {
            PunycodeAsset::register($view);
        }
        $options = $this->getClientOptions($validator, $model, $attribute);
        return 'yii.validation.email(value, messages, ' . Json::htmlEncode($options) . ');';
    }

    /**
     * Returns the client-side validation options.
     * @param \yii\validators\EmailValidator $validator the server-side validator.
     * @param \yii\base\Model $model the model being validated
     * @param string $attribute the attribute name being validated
     * @return array the client-side validation options
     */
    public function getClientOptions($validator, $model, $attribute)
    {
        $options = [
            'pattern' => new JsExpression($validator->pattern),
            'fullPattern' => new JsExpression($validator->fullPattern),
            'allowName' => $validator->allowName,
            'message' => $validator->formatMessage($validator->message, [
                'attribute' => $model->getAttributeLabel($attribute),
            ]),
            'enableIDN' => (bool)$validator->enableIDN,
        ];
        if ($validator->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}