<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\i18n\stubs;

use yii\i18n\PhpMessageSource;

/**
 * Test double exposing the protected {@see PhpMessageSource::getMessageFilePath()} for direct assertions.
 */
class ExposedPhpMessageSource extends PhpMessageSource
{
    public function exposeMessageFilePath($category, $language): string
    {
        return $this->getMessageFilePath($category, $language);
    }
}
