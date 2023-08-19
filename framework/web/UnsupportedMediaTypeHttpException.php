<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * UnsupportedMediaTypeHttpException represents an "Unsupported Media Type" HTTP exception with status code 415.
 *
 * Use this exception when the client sends data in a format that your
 * application does not understand. For example, you would throw this exception
 * if the client POSTs XML data to an action or controller that only accepts
 * JSON.
 *
 * @see https://tools.ietf.org/html/rfc7231#section-6.5.13
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 * @since 2.0
 */
class UnsupportedMediaTypeHttpException extends HttpException
{
    /**
     * Constructor.
     * @param string|null $message error message
     * @param int $code error code
     * @param \Throwable|null $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = 0, $previous = null)
    {
        parent::__construct(415, $message, $code, $previous);
    }
}
