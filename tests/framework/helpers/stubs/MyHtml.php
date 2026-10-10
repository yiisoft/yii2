<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers\stubs;

use yii\base\Model;
use yii\helpers\Html;

/**
 * Class MyHtml
 * @package yiiunit\framework\helpers
 */
class MyHtml extends Html
{
    /**
     * @param Model $model
     * @param string $attribute
     * @param array $options
     */
    protected static function setActivePlaceholder($model, $attribute, &$options = [])
    {
        if (isset($options['placeholder']) && $options['placeholder'] === true) {
            $attribute = static::getAttributeName($attribute);
            $options['placeholder'] = 'My placeholder: ' . $model->getAttributeLabel($attribute);
        }
    }
}
