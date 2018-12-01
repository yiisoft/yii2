<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ExitException 表示应用程序的正常终止。
 *
 * 不要捕获 ExitException。Yii 将处理此异常以优雅地终止应用程序。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ExitException extends \Exception
{
    /**
     * @var int 退出的状态码
     */
    public $statusCode;


    /**
     * 构造函数。
     * @param int $status 退出的状态码
     * @param string $message 错误信息
     * @param int $code 错误码
     * @param \Exception $previous 用于异常链接的前一个异常。
     */
    public function __construct($status = 0, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->statusCode = $status;
        parent::__construct($message, $code, $previous);
    }
}
