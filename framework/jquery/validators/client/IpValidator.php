<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jquery\validators\client;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\jquery\ValidationAsset;
use yii\validators\client\ClientValidator;
use yii\web\JsExpression;

/**
 * IpValidator composes client-side validation code from [[\yii\validators\IpValidator]].
 *
 * @see \yii\validators\IpValidator
 * @see ValidationAsset
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class IpValidator extends ClientValidator
{
    /**
     * @inheritdoc
     */
    public function build($validator, $model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($validator, $model, $attribute);
        return 'yii.validation.ip(value, messages, ' . Json::htmlEncode($options) . ');';
    }

    /**
     * Returns the client-side validation options.
     * @param \yii\validators\IpValidator $validator the server-side validator.
     * @param \yii\base\Model $model the model being validated
     * @param string $attribute the attribute name being validated
     * @return array the client-side validation options
     */
    public function getClientOptions($validator, $model, $attribute)
    {
        $messages = [
            'ipv6NotAllowed' => $validator->ipv6NotAllowed,
            'ipv4NotAllowed' => $validator->ipv4NotAllowed,
            'message' => $validator->message,
            'noSubnet' => $validator->noSubnet,
            'hasSubnet' => $validator->hasSubnet,
        ];
        foreach ($messages as &$message) {
            $message = $validator->formatMessage($message, [
                'attribute' => $model->getAttributeLabel($attribute),
            ]);
        }

        $options = [
            'ipv4Pattern' => new JsExpression(Html::escapeJsRegularExpression($validator->ipv4Pattern)),
            'ipv6Pattern' => new JsExpression(Html::escapeJsRegularExpression($validator->ipv6Pattern)),
            'messages' => $messages,
            'ipv4' => (bool) $validator->ipv4,
            'ipv6' => (bool) $validator->ipv6,
            'ipParsePattern' => new JsExpression(Html::escapeJsRegularExpression($validator->getIpParsePattern())),
            'negation' => $validator->negation,
            'subnet' => $validator->subnet,
        ];
        if ($validator->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}