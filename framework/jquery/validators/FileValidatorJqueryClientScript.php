<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yii\jquery\validators;

use Yii;
use yii\base\Model;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\validators\client\ClientValidatorScriptInterface;
use yii\validators\FileValidator;
use yii\validators\ImageValidator;
use yii\validators\ValidationAsset;
use yii\validators\Validator;
use yii\web\JsExpression;
use yii\web\View;

/**
 * FileValidatorJqueryClientScript provides client-side validation script generation for file attributes.
 *
 * This class implements {@see ClientValidatorScriptInterface} to supply client-side validation options and register the
 * corresponding JavaScript code for file validation in Yii2 forms using jQuery.
 *
 * @template T of FileValidator
 * @implements ClientValidatorScriptInterface<T>
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.2.0
 */
class FileValidatorJqueryClientScript implements ClientValidatorScriptInterface
{
    public function getClientOptions(Validator $validator, Model $model, string $attribute): array
    {
        $label = $model->getAttributeLabel($attribute);

        $options = [];

        if ($validator->message !== null) {
            $options['message'] = $validator->getFormattedClientMessage(
                $validator->message,
                ['attribute' => $label],
            );
        }

        $options['skipOnEmpty'] = $validator->skipOnEmpty;

        if (!$validator->skipOnEmpty) {
            $options['uploadRequired'] = $validator->getFormattedClientMessage(
                $validator->uploadRequired,
                ['attribute' => $label],
            );
        }

        if ($validator->mimeTypes !== null) {
            $mimeTypes = [];

            foreach ($validator->mimeTypes as $mimeType) {
                $mimeTypes[] = new JsExpression(
                    Html::escapeJsRegularExpression($this->buildMimeTypeRegexp($mimeType)),
                );
            }

            $options['mimeTypes'] = $mimeTypes;

            $options['wrongMimeType'] = $validator->getFormattedClientMessage(
                $validator->wrongMimeType,
                [
                    'attribute' => $label,
                    'mimeTypes' => implode(', ', $validator->mimeTypes),
                ],
            );
        }

        if ($validator->extensions !== null) {
            $options['extensions'] = $validator->extensions;

            $options['wrongExtension'] = $validator->getFormattedClientMessage(
                $validator->wrongExtension,
                [
                    'attribute' => $label,
                    'extensions' => implode(', ', $validator->extensions),
                ],
            );
        }

        if ($validator->minSize !== null) {
            $options['minSize'] = $validator->minSize;

            $options['tooSmall'] = $validator->getFormattedClientMessage(
                $validator->tooSmall,
                [
                    'attribute' => $label,
                    'limit' => $validator->minSize,
                    'formattedLimit' => Yii::$app->formatter->asShortSize($validator->minSize),
                ],
            );
        }

        if ($validator->maxSize !== null) {
            $options['maxSize'] = $validator->maxSize;

            $options['tooBig'] = $validator->getFormattedClientMessage(
                $validator->tooBig,
                [
                    'attribute' => $label,
                    'limit' => $validator->getSizeLimit(),
                    'formattedLimit' => Yii::$app->formatter->asShortSize($validator->getSizeLimit()),
                ],
            );
        }

        if ($validator->maxFiles !== null) {
            $options['maxFiles'] = $validator->maxFiles;

            $options['tooMany'] = $validator->getFormattedClientMessage(
                $validator->tooMany,
                [
                    'attribute' => $label,
                    'limit' => $validator->maxFiles,
                ],
            );
        }

        return $options;
    }

    public function register(Validator $validator, Model $model, string $attribute, View $view): string
    {
        ValidationAsset::register($view);

        $options = $this->getClientOptions($validator, $model, $attribute);

        if ($validator instanceof ImageValidator) {
            return 'yii.validation.image(attribute, messages, ' . Json::htmlEncode($options) . ', deferred);';
        }

        return 'yii.validation.file(attribute, messages, ' . Json::htmlEncode($options) . ');';
    }

    /**
     * Builds the RegExp from the $mask.
     *
     * @param string $mask the mime type mask, for example, `image/*`
     *
     * @return string the regular expression
     */
    private function buildMimeTypeRegexp(string $mask): string
    {
        return '/^' . str_replace('\*', '.*', preg_quote($mask, '/')) . '$/i';
    }
}
