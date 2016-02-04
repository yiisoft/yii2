<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\Exception;

/**
 * RedirectException represents an information for the HTTP location redirect.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.7
 */
class RedirectException extends Exception
{
    /**
     * @var string  the URL to be redirected to.
     */
    public $url;
    /**
     * @var integer the HTTP status code.
     */
    public $statusCode;

    /**
     * Constructor.
     * @param string $url the URL to be redirected to
     * @param integer $statusCode the HTTP status code. Defaults to 302.
     * @param string $message error message
     * @param integer $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($url, $statusCode = 302, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->url = $url;
        $this->statusCode = $statusCode;
        parent::__construct($message, $code, $previous);
    }
}