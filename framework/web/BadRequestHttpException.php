<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * BadRequestHttpException represents a "Bad Request" HTTP exception with status code 400.
 *
 * Use this exception to represent a generic client error. In many cases, there
 * may be an HTTP exception that more precisely describes the error. In that
 * case, consider using the more precise exception to provide the user with
 * additional information.
 *
 * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.4.1
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BadRequestHttpException extends HttpException
{
    /**
     * Constructor.
     * @param string $message error message
     * @param integer $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(400, $message, $code, $previous);
    }
}
