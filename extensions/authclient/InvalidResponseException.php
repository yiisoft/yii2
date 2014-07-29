<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient;

use yii\base\Exception;

/**
 * InvalidResponseException represents an exception caused by invalid remote server response.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class InvalidResponseException extends Exception
{
    /**
     * @var array response headers.
     */
    public $responseHeaders = [];
    /**
     * @var string response body.
     */
    public $responseBody = '';

    /**
     * Constructor.
     * @param array $responseHeaders response headers
     * @param string $responseBody response body
     * @param string $message error message
     * @param integer $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     * @internal param int $status HTTP status code, such as 404, 500, etc.
     */
    public function __construct($responseHeaders, $responseBody, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->responseBody = $responseBody;
        $this->responseHeaders = $responseHeaders;
        parent::__construct($message, $code, $previous);
    }
}