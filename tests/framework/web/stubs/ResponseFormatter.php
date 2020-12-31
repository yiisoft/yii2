<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\stubs;

use yii\web\ResponseFormatterInterface;

class ResponseFormatter implements ResponseFormatterInterface
{
    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function format($request)
    {
        $request->content = $this->message;
    }
}
