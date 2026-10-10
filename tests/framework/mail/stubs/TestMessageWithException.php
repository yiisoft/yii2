<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\mail\stubs;

use Exception;

class TestMessageWithException extends TestMessage
{
    public function toString()
    {
        throw new Exception('Test exception in toString.');
    }
}
