<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yii\jquery\validators;

use yii\base\Model;
use yii\helpers\Json;
use yii\validators\client\ClientValidatorScriptInterface;
use yii\validators\TrimValidator;
use yii\validators\ValidationAsset;
use yii\validators\Validator;
use yii\web\View;

/**
 * TrimValidatorJqueryClientScript provides client-side validation script generation for trimming attributes.
 *
 * This class implements {@see ClientValidatorScriptInterface} to supply client-side validation options and register the
 * corresponding JavaScript code for trim validation in Yii2 forms using jQuery.
 *
 * @template T of TrimValidator
 * @implements ClientValidatorScriptInterface<T>
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.2.0
 */
class TrimValidatorJqueryClientScript implements ClientValidatorScriptInterface
{
    public function getClientOptions(Validator $validator, Model $model, string $attribute): array
    {
        return [
            'skipOnArray' => (bool) $validator->skipOnArray,
            'skipOnEmpty' => (bool) $validator->skipOnEmpty,
            'chars' => $validator->chars ?: false,
        ];
    }

    public function register(Validator $validator, Model $model, string $attribute, View $view): string
    {
        if ($validator->skipOnArray && is_array($model->$attribute)) {
            return '';
        }

        ValidationAsset::register($view);

        $options = $this->getClientOptions($validator, $model, $attribute);

        return 'value = yii.validation.trim($form, attribute, ' . Json::htmlEncode($options) . ', value);';
    }
}
