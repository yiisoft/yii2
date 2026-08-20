<?php

declare(strict_types=1);

namespace yiiunit\framework\log\mocks;

use yii\log\{FileTarget, Logger};

use function is_string;

/**
 * File target test double with deterministic message suppression.
 *
 * @phpstan-import-type LogMessage from Logger
 */
class CustomLogger extends FileTarget
{
    /**
     * @param LogMessage $message
     *
     * @return string|null
     */
    public function formatMessage($message)
    {
        if ($message[0] === 'yyy' || $message[0] === 'null') {
            return null;
        }

        return is_string($message[0]) ? $message[0] : parent::formatMessage($message);
    }
}
