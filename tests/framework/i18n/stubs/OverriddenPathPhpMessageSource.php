<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\i18n\stubs;

use Yii;
use yii\i18n\PhpMessageSource;

/**
 * Test double overriding {@see PhpMessageSource::getMessageFilePath()} without calling the parent implementation.
 *
 * Records every category the override receives, so tests can assert which categories reach it.
 */
class OverriddenPathPhpMessageSource extends PhpMessageSource
{
    /**
     * @var string[] categories passed to {@see getMessageFilePath()}, in call order.
     */
    public $requestedCategories = [];

    protected function getMessageFilePath($category, $language)
    {
        $this->requestedCategories[] = $category;

        $messageFile = Yii::getAlias($this->basePath) . "/$language/";

        if (isset($this->fileMap[$category])) {
            return $messageFile . $this->fileMap[$category];
        }

        return $messageFile . str_replace('\\', '/', $category) . '.php';
    }
}
