<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * UnprocessableEntityHttpException represents an "Unprocessable Entity" HTTP
 * exception with status code 422.
 *
 * Use this exception to inform that the server understands the content type of
 * the request entity and the syntax of that request entity is correct but the server
 * was unable to process the contained instructions. For example, to return form
 * validation errors.
 *
 * @link http://www.webdav.org/specs/rfc2518.html#STATUS_422
 * @author Jan Silva <janfrs3@gmail.com>
 * @since 2.0.7
 */
class UnprocessableEntityHttpException extends HttpException
{
    /**
     * Constructor.
     * @param string|null $message error message
     * @param int $code error code
     * @param \Throwable|null $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = 0, $previous = null)
    {
        parent::__construct(422, $message, $code, $previous);
    }
}
