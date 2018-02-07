<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jquery\validators\client;

use yii\jquery\ValidationAsset;

/**
 * ImageValidator composes client-side validation code from [[\yii\validators\ImageValidator]].
 *
 * @see \yii\validators\ImageValidator
 * @see ValidationAsset
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class ImageValidator extends FileValidator
{
    /**
     * {@inheritdoc}
     */
    public function build($validator, $model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($validator, $model, $attribute);
        return 'yii.validation.image(attribute, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ', deferred);';
    }

    /**
     * Returns the client-side validation options.
     * @param \yii\validators\ImageValidator $validator the server-side validator.
     * @param \yii\base\Model $model the model being validated
     * @param string $attribute the attribute name being validated
     * @return array the client-side validation options
     */
    public function getClientOptions($validator, $model, $attribute)
    {
        $options = parent::getClientOptions($validator, $model, $attribute);

        $label = $model->getAttributeLabel($attribute);

        if ($validator->notImage !== null) {
            $options['notImage'] = $validator->formatMessage($validator->notImage, [
                'attribute' => $label,
            ]);
        }

        if ($validator->minWidth !== null) {
            $options['minWidth'] = $validator->minWidth;
            $options['underWidth'] = $validator->formatMessage($validator->underWidth, [
                'attribute' => $label,
                'limit' => $validator->minWidth,
            ]);
        }

        if ($validator->maxWidth !== null) {
            $options['maxWidth'] = $validator->maxWidth;
            $options['overWidth'] = $validator->formatMessage($validator->overWidth, [
                'attribute' => $label,
                'limit' => $validator->maxWidth,
            ]);
        }

        if ($validator->minHeight !== null) {
            $options['minHeight'] = $validator->minHeight;
            $options['underHeight'] = $validator->formatMessage($validator->underHeight, [
                'attribute' => $label,
                'limit' => $validator->minHeight,
            ]);
        }

        if ($validator->maxHeight !== null) {
            $options['maxHeight'] = $validator->maxHeight;
            $options['overHeight'] = $validator->formatMessage($validator->overHeight, [
                'attribute' => $label,
                'limit' => $validator->maxHeight,
            ]);
        }

        return $options;
    }
}