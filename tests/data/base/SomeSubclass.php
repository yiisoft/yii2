<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\base;

/**
 * Stub subclass of {@see SomeClass} used to verify inherited class-level event handling.
 */
final class SomeSubclass extends SomeClass
{
    public function emitEventInSubclass(): void
    {
        $this->trigger(self::EVENT_SUPER_EVENT);
    }
}
