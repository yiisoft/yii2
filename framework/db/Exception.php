<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * Exception 表示由某些与 DB 相关操作引起的异常。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Exception extends \yii\base\Exception
{
    /**
     * @var array PDO 异常提供的错误信息。
     * 这与 [PDO::errorInfo](http://www.php.net/manual/en/pdo.errorinfo.php) 返回的相同。
     */
    public $errorInfo = [];


    /**
     * 构造函数。
     * @param string $message PDO 错误消息
     * @param array $errorInfo PDO 错误信息
     * @param int $code PDO 错误代码
     * @param \Exception $previous 用于异常链接的先前异常。
     */
    public function __construct($message, $errorInfo = [], $code = 0, \Exception $previous = null)
    {
        $this->errorInfo = $errorInfo;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string 此异常的用户友好的名称
     */
    public function getName()
    {
        return 'Database Exception';
    }

    /**
     * @return string 可读的异常表示
     */
    public function __toString()
    {
        return parent::__toString() . PHP_EOL
        . 'Additional Information:' . PHP_EOL . print_r($this->errorInfo, true);
    }
}
