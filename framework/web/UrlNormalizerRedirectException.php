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
     * @var string route used to generate URL for redirection
     */
    public $route;
    /**
     * @var array params used to generate URL for redirection
     */
    public $params;
    /**
     * @var integer the HTTP status code
     */
    public $statusCode;

    /**
     * @param string $route route used to generate URL for redirection
     * @param string $params params used to generate URL for redirection
     * @param integer $statusCode HTTP status code used for redirection
     * @param string $message the error message
     * @param integer $code the error code
     * @param \Exception $previous the previous exception used for the exception chaining
     */
    public function __construct($route, $params = [], $statusCode = 302, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->route = $route;
        $this->params = $params;
        $this->statusCode = $statusCode;
        parent::__construct($message, $code, $previous);
    }
}
