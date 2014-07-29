<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\httpclient;

use yii\base\Object;
use Yii;

/**
 * Class Client
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Client extends Object
{
    /**
     * @var string
     */
    public $baseUrl;
    /**
     * @var array
     */
    public $requestConfig = [];
    /**
     * @var array
     */
    public $responseConfig = [];

    /**
     * @return Request request instance.
     */
    public function createRequest()
    {
        $config = $this->requestConfig;
        if (!isset($config['class'])) {
            $config['class'] = Request::className();
        }
        $config['client'] = $this;
        return Yii::createObject($config);
    }

    /**
     * @return Response request instance.
     */
    public function createResponse()
    {
        $config = $this->responseConfig;
        if (!isset($config['class'])) {
            $config['class'] = Response::className();
        }
        return Yii::createObject($config);
    }

    public function send($request)
    {
        ;
    }

    public function batchSend(array $requests)
    {
        ;
    }
} 