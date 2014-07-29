<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\httpclient;

use yii\base\Exception;
use yii\base\Object;
use Yii;
use yii\helpers\ArrayHelper;

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

    /**
     * @param Request $request request to be sent.
     * @return Response response instance.
     * @throws Exception
     */
    public function send($request)
    {
        $curlOptions = ArrayHelper::merge(
            $request->getOptions(),
            [
                CURLOPT_HTTPHEADER => $this->composeHeaders($request),
                CURLOPT_RETURNTRANSFER => true,
            ]
        );

        $method = strtoupper($request->getMethod());
        switch ($method) {
            case 'GET': {
                $url = $this->composeUrl($request, true);
                break;
            }
            case 'POST': {
                $url = $this->composeUrl($request);
                $curlOptions[CURLOPT_POST] = true;
                $curlOptions[CURLOPT_POSTFIELDS] = $request->getContent();
                break;
            }
            case 'HEAD': {
                $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
                $url = $this->composeUrl($request, true);
                break;
            }
            default: {
                $url = $this->composeUrl($request);
                $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
                $curlOptions[CURLOPT_POSTFIELDS] = $request->getContent();
            }
        }

        $curlOptions[CURLOPT_URL] = $url;

        $curlResource = curl_init();
        foreach ($curlOptions as $option => $value) {
            curl_setopt($curlResource, $option, $value);
        }
        $responseContent = curl_exec($curlResource);
        $responseHeaders = curl_getinfo($curlResource);

        // check cURL error
        $errorNumber = curl_errno($curlResource);
        $errorMessage = curl_error($curlResource);

        curl_close($curlResource);

        if ($errorNumber > 0) {
            throw new Exception('Curl error requesting "' .  $url . '": #' . $errorNumber . ' - ' . $errorMessage);
        }

        $response = $this->createResponse();
        $response->setContent($responseContent);
        $response->setHeaders($responseHeaders);
        return $response;
    }

    public function batchSend(array $requests)
    {
        // @todo
    }

    /**
     * Composes actual request URL string.
     * @param Request $request request instance.
     * @param boolean $appendData whether to append request data to the URL as GET parameters.
     * @return string composed URL.
     */
    protected function composeUrl($request, $appendData = false)
    {
        $requestUrl = $request->getUrl();
        if (preg_match('/^https?:\\/\\//is', $requestUrl)) {
            $url = $requestUrl;
        } else {
            $url = $this->baseUrl . '/' . $requestUrl;
        }

        if ($appendData) {
            $data = $request->getData();
            if (!empty($data)) {
                if (strpos($url, '?') === false) {
                    $url .= '?';
                } else {
                    $url .= '&';
                }
                $url .= http_build_query($data, '', '&', PHP_QUERY_RFC3986);
            }
        }
        return $url;
    }

    /**
     * Composes request headers for the cURL.
     * @param Request $request request instance.
     * @return array headers list.
     */
    protected function composeHeaders($request)
    {
        $headers = [];
        foreach ($request->getHeaders() as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            foreach ($values as $value) {
                $headers[] = "$name: $value";
            }
        }
        return $headers;
    }
} 