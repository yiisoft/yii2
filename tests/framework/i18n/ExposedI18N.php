<?php

namespace yiiunit\framework\i18n;

use yii\i18n\I18N;

/**
 * ExposedI18N is a version of I18N component with
 * protected methods exposed for testing purposes
 *
 * @package yiiunit\framework\i18n
 */
class ExposedI18N extends I18N
{
    /**
     * @inheritdoc
     */
    public function fallbackNormalizeLocale($locale)
    {
        return parent::fallbackNormalizeLocale($locale);
    }
}