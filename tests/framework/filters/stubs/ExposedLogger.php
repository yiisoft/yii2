<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\filters\stubs;


use yii\log\Logger;

class ExposedLogger extends Logger
{
    public function log($message, $level, $category = 'application')
    {
        $this->messages[] = $message;
    }
}
