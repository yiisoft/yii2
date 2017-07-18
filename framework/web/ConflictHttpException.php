<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * ConflictHttpException represents a "Conflict" HTTP exception with status code 409
 *
 * @see https://tools.ietf.org/html/rfc7231#section-6.5.8
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 * @since 2.0
 */
class ConflictHttpException extends HttpException
{
    /**
     * Constructor.
     * @param string $message error message
     * @param int $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(409, $message, $code, $previous);
    }
}
