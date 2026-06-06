<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\stubs;

use yii\web\ErrorHandler;

class FallbackMessageErrorHandler extends ErrorHandler
{
    public function callRenderFallbackExceptionMessage($exception, $previousException, &$log = '')
    {
        return $this->renderFallbackExceptionMessage($exception, $previousException, $log);
    }
}
