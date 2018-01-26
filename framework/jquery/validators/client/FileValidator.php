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
 * FileValidator composes client-side validation code from [[\yii\validators\FileValidator]].
 *
 * @see \yii\validators\FileValidator
 * @see ValidationAsset
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class FileValidator extends ClientValidator
{
    /**
     * {@inheritdoc}
     */
    public function build($validator, $model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($validator, $model, $attribute);
        return 'yii.validation.file(attribute, messages, ' . Json::encode($options) . ');';
    }

    /**
     * Returns the client-side validation options.
     * @param \yii\validators\FileValidator $validator the server-side validator.
     * @param \yii\base\Model $model the model being validated
     * @param string $attribute the attribute name being validated
     * @return array the client-side validation options
     */
    public function getClientOptions($validator, $model, $attribute)
    {
        $label = $model->getAttributeLabel($attribute);

        $options = [];
        if ($validator->message !== null) {
            $options['message'] = $validator->formatMessage($validator->message, [
                'attribute' => $label,
            ]);
        }

        $options['skipOnEmpty'] = $validator->skipOnEmpty;

        if (!$validator->skipOnEmpty) {
            $options['uploadRequired'] = $validator->formatMessage($validator->uploadRequired, [
                'attribute' => $label,
            ]);
        }

        if ($validator->mimeTypes !== null) {
            $mimeTypes = [];
            foreach ($validator->mimeTypes as $mimeType) {
                $mimeTypes[] = new JsExpression(Html::escapeJsRegularExpression($validator->buildMimeTypeRegexp($mimeType)));
            }
            $options['mimeTypes'] = $mimeTypes;
            $options['wrongMimeType'] = $validator->formatMessage($validator->wrongMimeType, [
                'attribute' => $label,
                'mimeTypes' => implode(', ', $validator->mimeTypes),
            ]);
        }

        if ($validator->extensions !== null) {
            $options['extensions'] = $validator->extensions;
            $options['wrongExtension'] = $validator->formatMessage($validator->wrongExtension, [
                'attribute' => $label,
                'extensions' => implode(', ', $validator->extensions),
            ]);
        }

        if ($validator->minSize !== null) {
            $options['minSize'] = $validator->minSize;
            $options['tooSmall'] = $validator->formatMessage($validator->tooSmall, [
                'attribute' => $label,
                'limit' => $validator->minSize,
                'formattedLimit' => Yii::$app->formatter->asShortSize($validator->minSize),
            ]);
        }

        if ($validator->maxSize !== null) {
            $options['maxSize'] = $validator->maxSize;
            $options['tooBig'] = $validator->formatMessage($validator->tooBig, [
                'attribute' => $label,
                'limit' => $validator->getSizeLimit(),
                'formattedLimit' => Yii::$app->formatter->asShortSize($validator->getSizeLimit()),
            ]);
        }

        if ($validator->maxFiles !== null) {
            $options['maxFiles'] = $validator->maxFiles;
            $options['tooMany'] = $validator->formatMessage($validator->tooMany, [
                'attribute' => $label,
                'limit' => $validator->maxFiles,
            ]);
        }

        return $options;
    }
}