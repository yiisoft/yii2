<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\log;

use yiiunit\framework\log\DispatcherTest;

function microtime(bool $asFloat): string|float
{
    if (DispatcherTest::$microtimeIsMocked) {
        return DispatcherTest::microtime($asFloat);
    }

    return \microtime($asFloat);
}
