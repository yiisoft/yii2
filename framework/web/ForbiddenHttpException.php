<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * ForbiddenHttpException represents a "Forbidden" HTTP exception with status code 403.
 *
 * Use this exception when a user is not allowed to perform the requested action.
 * Using different credentials might or might not allow performing the requested action.
 * If you do not want to expose authorization information to the user, it is valid
 * to respond with a 404 [[NotFoundHttpException]].
 *
 * @see https://tools.ietf.org/html/rfc7231#section-6.5.3
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 * @since 2.0
 */
class ForbiddenHttpException extends HttpException
{
    /**
     * Constructor.
     * @param string|null $message error message
     * @param int $code error code
     * @param \Throwable|null $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = 0, $previous = null)
    {
        parent::__construct(403, $message, $code, $previous);
    }
}
