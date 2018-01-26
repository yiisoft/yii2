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
 * CaptchaClientValidator composes client-side validation code from [[CaptchaValidator]].
 *
 * @see CaptchaValidator
 * @see ValidationAsset
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class CaptchaClientValidator extends ClientValidator
{
    /**
     * {@inheritdoc}
     */
    public function build($validator, $model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($validator, $model, $attribute);
        return 'yii.validation.captcha(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

    /**
     * Returns the client-side validation options.
     * @param \yii\captcha\CaptchaValidator $validator the server-side validator.
     * @param \yii\base\Model $model the model being validated
     * @param string $attribute the attribute name being validated
     * @return array the client-side validation options
     */
    public function getClientOptions($validator, $model, $attribute)
    {
        $captcha = $validator->createCaptchaAction();
        $code = $captcha->getVerifyCode(false);
        $hash = $captcha->generateValidationHash($validator->caseSensitive ? $code : strtolower($code));
        $options = [
            'hash' => $hash,
            'hashKey' => 'yiiCaptcha/' . $captcha->getUniqueId(),
            'caseSensitive' => $validator->caseSensitive,
            'message' => $validator->formatMessage($validator->message, [
                'attribute' => $model->getAttributeLabel($attribute),
            ]),
        ];
        if ($validator->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}