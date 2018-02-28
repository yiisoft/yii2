<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * UrlNormalizerRedirectException represents an information for redirection which should be
 * performed during the URL normalization.
 *
 * @author Robert Korulczyk <robert@korulczyk.pl>
 * @since 2.0.10
 */
class UrlNormalizerRedirectException extends \yii\base\Exception
{
    /**
     * @var array|string the parameter to be used to generate a valid URL for redirection
     * @see [[\yii\helpers\Url::to()]]
     */
    public $url;
    /**
     * @var bool|string the URI scheme to use in the generated URL for redirection
     * @see [[\yii\helpers\Url::to()]]
     */
    public $scheme;
    /**
     * @var int the HTTP status code
     */
    public $statusCode;


    /**
     * @param array|string $url the parameter to be used to generate a valid URL for redirection.
     * This will be used as first parameter for [[\yii\helpers\Url::to()]]
     * @param int $statusCode HTTP status code used for redirection
     * @param bool|string $scheme the URI scheme to use in the generated URL for redirection.
     * This will be used as second parameter for [[\yii\helpers\Url::to()]]
     * @param string $message the error message
     * @param int $code the error code
     * @param \Exception $previous the previous exception used for the exception chaining
     */
    public function __construct($url, $statusCode = 302, $scheme = false, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->url = $url;
        $this->scheme = $scheme;
        $this->statusCode = $statusCode;
        parent::__construct($message, $code, $previous);
    }
}
