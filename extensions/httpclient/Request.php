<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\httpclient;

use yii\base\Object;

/**
 * Class Request
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Request extends Object implements DocumentInterface
{
    use DocumentTrait;

    /**
     * @var Client
     */
    public $client;
    /**
     * @var string request method.
     */
    private $_method = 'get';
    /**
     * @var array CURL options
     */
    private $_options = [];

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