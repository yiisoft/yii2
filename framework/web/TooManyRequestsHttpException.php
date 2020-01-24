<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * TooManyRequestsHttpException represents a "Too Many Requests" HTTP exception with status code 429.
 *
 * Use this exception to indicate that a client has made too many requests in a
 * given period of time. For example, you would throw this exception when
 * 'throttling' an API user.
 *
 * @see https://tools.ietf.org/html/rfc6585#section-4
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 * @since 2.0
 */
class TooManyRequestsHttpException extends HttpException
{
    /**
     * Constructor.
     * @param string $message error message
     * @param int $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(429, $message, $code, $previous);
    }
}
