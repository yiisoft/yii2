<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * NotFoundHttpException represents a "Not Found" HTTP exception with status code 404.
 *
 * @see https://tools.ietf.org/html/rfc7231#section-6.5.4
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class NotFoundHttpException extends HttpException
{
    /**
     * Constructor.
     * @param string $message error message
     * @param int $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(404, $message, $code, $previous);
    }
}
