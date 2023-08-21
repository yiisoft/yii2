<?php

namespace yiiunit\framework\log\mocks;

use yii\log\FileTarget;

class CustomLogger extends FileTarget
{
    /**
     * @param array $message
     *
     * @return null|string|array
     */
    public function formatMessage($message)
    {
        if ($message == 'yyy') {
            return null;
        }

        return $message;
    }
}
