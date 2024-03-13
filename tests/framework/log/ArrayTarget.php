<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use yii\base\Exception;
use yii\log\Target;

/**
 * ArrayTarget logs messages into an array, useful for tracking data in tests.
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
