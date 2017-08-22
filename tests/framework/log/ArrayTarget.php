<?php

namespace yiiunit\framework\log;

use yii\base\Exception;
use yii\log\Target;

/**
 * A log target used to track logged data
 */
class ArrayTarget extends Target
{
    public $exportInterval = 1000000;

    /**
     * Exports log [[messages]] to a specific destination.
     */
    public function export()
    {
        // throw exception if message limit is reached
        throw new Exception('More than 1000000 messages logged.');
    }
}
