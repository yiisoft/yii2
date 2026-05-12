<?php

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
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.0.56
 */
class ErrorHandlerRenderEvent extends Event
{
    /**
     * @var \Throwable|null Exception being rendered.
     */
    public ?Throwable $exception = null;
    /**
     * @var string Rendered HTML output.
     */
    public string $output = '';
}
