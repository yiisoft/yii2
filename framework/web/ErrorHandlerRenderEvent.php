<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\web;

use Throwable;
use yii\base\Event;

/**
 * ErrorHandlerRenderEvent represents events triggered by [[ErrorHandler]].
 *
 * @since 22.0
 */
class ErrorHandlerRenderEvent extends Event
{
    /**
     * Exception being rendered.
     */
    public Throwable|null $exception = null;
    /**
     * Rendered HTML output.
     */
    public string $output = "";
}

