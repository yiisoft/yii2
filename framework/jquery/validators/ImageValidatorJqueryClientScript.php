<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yii\jquery\validators;

use yii\base\Model;
use yii\validators\ImageValidator;
use yii\validators\Validator;

/**
 * ImageValidatorJqueryClientScript provides client-side validation script generation for image attributes.
 *
 * This class implements {@see ClientValidatorScriptInterface} to supply client-side validation options and register the
 * corresponding JavaScript code for image validation in Yii2 forms using jQuery.
 *
 * @extends FileValidatorJqueryClientScript<ImageValidator>
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.2.0
 */
class ImageValidatorJqueryClientScript extends FileValidatorJqueryClientScript
{
    public function getClientOptions(Validator $validator, Model $model, string $attribute): array
    {
        $label = $model->getAttributeLabel($attribute);
        $options = parent::getClientOptions($validator, $model, $attribute);

        if ($validator->notImage !== null) {
            $options['notImage'] = $validator->getFormattedClientMessage(
                $validator->notImage,
                ['attribute' => $label],
            );
        }

        if ($validator->minWidth !== null) {
            $options['minWidth'] = $validator->minWidth;

            $options['underWidth'] = $validator->getFormattedClientMessage(
                $validator->underWidth,
                [
                    'attribute' => $label,
                    'limit' => $validator->minWidth,
                ],
            );
        }

        if ($validator->maxWidth !== null) {
            $options['maxWidth'] = $validator->maxWidth;

            $options['overWidth'] = $validator->getFormattedClientMessage(
                $validator->overWidth,
                [
                    'attribute' => $label,
                    'limit' => $validator->maxWidth,
                ],
            );
        }

        if ($validator->minHeight !== null) {
            $options['minHeight'] = $validator->minHeight;

            $options['underHeight'] = $validator->getFormattedClientMessage(
                $validator->underHeight,
                [
                    'attribute' => $label,
                    'limit' => $validator->minHeight,
                ],
            );
        }

        if ($validator->maxHeight !== null) {
            $options['maxHeight'] = $validator->maxHeight;

            $options['overHeight'] = $validator->getFormattedClientMessage(
                $validator->overHeight,
                [
                    'attribute' => $label,
                    'limit' => $validator->maxHeight,
                ],
            );
        }

        return $options;
    }
}
