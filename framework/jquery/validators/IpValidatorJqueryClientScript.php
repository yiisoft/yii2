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
use yii\web\JsExpression;
use yii\web\View;

/**
 * IpValidatorJqueryClientScript provides client-side validation script generation for IP address attributes.
 *
 * This class implements {@see ClientValidatorScriptInterface} to supply client-side validation options and register
 * the corresponding JavaScript code for IP address validation in Yii2 forms using jQuery.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.2.0
 */
class IpValidatorJqueryClientScript implements ClientValidatorScriptInterface
{
    public function getClientOptions(Validator $validator, Model $model, string $attribute): array
    {
        $messages = [
            'ipv6NotAllowed' => $validator->ipv6NotAllowed,
            'ipv4NotAllowed' => $validator->ipv4NotAllowed,
            'message' => $validator->message,
            'noSubnet' => $validator->noSubnet,
            'hasSubnet' => $validator->hasSubnet,
        ];

        foreach ($messages as &$message) {
            $message = $validator->formatMessage(
                $message,
                ['attribute' => $model->getAttributeLabel($attribute)],
            );
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

    public function register(Validator $validator, Model $model, string $attribute, View $view): string
    {
        ValidationAsset::register($view);

        $options = $this->getClientOptions($validator, $model, $attribute);

        return 'yii.validation.ip(value, messages, ' . Json::htmlEncode($options) . ');';
    }
}
