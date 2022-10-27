<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * UnauthorizedHttpException represents an "Unauthorized" HTTP exception with status code 401.
 *
 * Use this exception to indicate that a client needs to authenticate via WWW-Authenticate header
 * to perform the requested action.
 *
 * If the client is already authenticated and is simply not allowed to
 * perform the action, consider using a 403 [[ForbiddenHttpException]]
 * or 404 [[NotFoundHttpException]] instead.
 *
 * @link https://tools.ietf.org/html/rfc7235#section-3.1
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 * @since 2.0
 */
class UnauthorizedHttpException extends HttpException
{
    /**
     * Constructor.
     * @param string|null $message error message
     * @param int $code error code
     * @param \Throwable|null $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = 0, $previous = null)
    {
        parent::__construct(401, $message, $code, $previous);
    }
}
