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
use yii\validators\ValidationAsset;
use yii\validators\Validator;
use yii\web\View;

/**
 * ImageValidatorJqueryClientScript provides client-side validation script generation for image attributes.
 *
 * This class implements {@see ClientValidatorScriptInterface} to supply client-side validation options and register
 * the corresponding JavaScript code for image validation in Yii2 forms using jQuery.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.2.0
 */
class ImageValidatorJqueryClientScript implements ClientValidatorScriptInterface
{
    public function getClientOptions(Validator $validator, Model $model, string $attribute): array
    {
        $options = $validator->getClientOptions($model, $attribute);

        $label = $model->getAttributeLabel($attribute);

        if ($validator->notImage !== null) {
            $options['notImage'] = $validator->formatMessage(
                $validator->notImage,
                ['attribute' => $label],
            );
        }

        if ($validator->minWidth !== null) {
            $options['minWidth'] = $validator->minWidth;

            $options['underWidth'] = $validator->formatMessage(
                $validator->underWidth,
                [
                    'attribute' => $label,
                    'limit' => $validator->minWidth,
                ],
            );
        }

        if ($validator->maxWidth !== null) {
            $options['maxWidth'] = $validator->maxWidth;

            $options['overWidth'] = $validator->formatMessage(
                $validator->overWidth,
                [
                    'attribute' => $label,
                    'limit' => $validator->maxWidth,
                ],
            );
        }

        if ($validator->minHeight !== null) {
            $options['minHeight'] = $validator->minHeight;

            $options['underHeight'] = $validator->formatMessage(
                $validator->underHeight,
                [
                    'attribute' => $label,
                    'limit' => $validator->minHeight,
                ],
            );
        }

        if ($validator->maxHeight !== null) {
            $options['maxHeight'] = $validator->maxHeight;

            $options['overHeight'] = $validator->formatMessage(
                $validator->overHeight,
                [
                    'attribute' => $label,
                    'limit' => $validator->maxHeight,
                ],
            );
        }

        return $options;
    }

    public function register(Validator $validator, Model $model, string $attribute, View $view): string
    {
        ValidationAsset::register($view);

        $options = $this->getClientOptions($validator, $model, $attribute);

        return 'yii.validation.image(attribute, messages, ' . Json::htmlEncode($options) . ', deferred);';
    }
}
