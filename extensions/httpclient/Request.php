<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\httpclient;

use yii\base\Object;

/**
 * Request represents HTTP request.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Request extends Object implements DocumentInterface
{
    use DocumentTrait;

    /**
     * @var Client owner client instance.
     */
    public $client;
    /**
     * @var string
     */
    private $_url;
    /**
     * @var string request method.
     */
    private $_method = 'get';
    /**
     * @var array CURL options
     */
    private $_options = [];

    /**
     * @param string $url
     * @return static self reference.
     */
    public function setUrl($url)
    {
        $this->_url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * @param string $method
     * @return static self reference.
     */
    public function setMethod($method)
    {
        $this->_method = $method;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * @param array $options
     * @return static self reference.
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    public function send()
    {
        return $this->client->send($this);
    }
}