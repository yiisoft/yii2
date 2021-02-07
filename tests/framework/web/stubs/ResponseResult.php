<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\stubs;

use yii\web\DataResponseInterface;

class ResponseResult implements DataResponseInterface
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
