<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\log;

use yii\log\Target;

/**
 * Log target stub that exposes context collection and records exported messages.
 */
final class TargetStub extends Target
{
    /**
     * @var list<array> exported log messages.
     */
    public static array $exportedMessages = [];

    public $exportInterval = 1;

    public function export(): void
    {
        self::$exportedMessages = array_merge(self::$exportedMessages, $this->messages);
        $this->messages = [];
    }

    public function getContextMessage()
    {
        return parent::getContextMessage();
    }
}
