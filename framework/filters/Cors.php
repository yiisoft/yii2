<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters;

use Yii;
use yii\base\ActionFilter;
use yii\base\InvalidConfigException;
use yii\web\Request;
use yii\web\Response;

/**
 * Cors 过滤工具 [Cross Origin Resource Sharing](http://en.wikipedia.org/wiki/Cross-origin_resource_sharing).
 *
 * 请务必仔细阅读 CORS 能做的和不能做的事情。CORS 不保护您的 API，
 * 但是允许开发人员授予对第三方代码的访问权限（来自外部域的 Ajax 调用）。
 *
 * 您可以通过将 CORS 筛选器作为行为附加到控制器或模块来使用 CORS 筛选器，如下所示：
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'corsFilter' => [
 *             'class' => \yii\filters\Cors::className(),
 *         ],
 *     ];
 * }
 * ```
 *
 * CORS 过滤器可以专门用于限制参数，如下所示，
 * [MDN CORS Information](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS)
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'corsFilter' => [
 *             'class' => \yii\filters\Cors::className(),
 *             'cors' => [
 *                 // restrict access to
 *                 'Origin' => ['http://www.myserver.com', 'https://www.myserver.com'],
 *                 // Allow only POST and PUT methods
 *                 'Access-Control-Request-Method' => ['POST', 'PUT'],
 *                 // Allow only headers 'X-Wsse'
 *                 'Access-Control-Request-Headers' => ['X-Wsse'],
 *                 // Allow credentials (cookies, authorization headers, etc.) to be exposed to the browser
 *                 'Access-Control-Allow-Credentials' => true,
 *                 // Allow OPTIONS caching
 *                 'Access-Control-Max-Age' => 3600,
 *                 // Allow the X-Pagination-Current-Page header to be exposed to the browser.
 *                 'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
 *             ],
 *
 *         ],
 *     ];
 * }
 * ```
 *
 * 有关如何将 CORS 过滤器添加到控制器的详细信息，
 * 请参见 [Guide on REST controllers](guide:rest-controllers#cors)。
 *
 * @author Philippe Gaultier <pgaultier@gmail.com>
 * @since 2.0
 */
class Cors extends ActionFilter
{
    /**
     * @var Request 当前请求。如果未设置，将使用 `request` 应用程序组件。
     */
    public $request;
    /**
     * @var Response 要发送的响应。如果未设置，将使用 `response` 应用程序组件。
     */
    public $response;
    /**
     * @var array 定义特定操作的特定 CORS 规则
     */
    public $actions = [];
    /**
     * @var array  为CORS请求处理的 Basic headers。
     */
    public $cors = [
        'Origin' => ['*'],
        'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
        'Access-Control-Request-Headers' => ['*'],
        'Access-Control-Allow-Credentials' => null,
        'Access-Control-Max-Age' => 86400,
        'Access-Control-Expose-Headers' => [],
    ];


    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $this->request = $this->request ?: Yii::$app->getRequest();
        $this->response = $this->response ?: Yii::$app->getResponse();

        $this->overrideDefaultSettings($action);

        $requestCorsHeaders = $this->extractHeaders();
        $responseCorsHeaders = $this->prepareHeaders($requestCorsHeaders);
        $this->addCorsHeaders($this->response, $responseCorsHeaders);

        if ($this->request->isOptions && $this->request->headers->has('Access-Control-Request-Method')) {
            // it is CORS preflight request, respond with 200 OK without further processing
            $this->response->setStatusCode(200);
            return false;
        }

        return true;
    }

    /**
     * 覆盖特定操作的设置。
     * @param \yii\base\Action $action 要覆盖的操作设置
     */
    public function overrideDefaultSettings($action)
    {
        if (isset($this->actions[$action->id])) {
            $actionParams = $this->actions[$action->id];
            $actionParamsKeys = array_keys($actionParams);
            foreach ($this->cors as $headerField => $headerValue) {
                if (in_array($headerField, $actionParamsKeys)) {
                    $this->cors[$headerField] = $actionParams[$headerField];
                }
            }
        }
    }

    /**
     * 从请求中提取 CORS 标头。
     * @return array 要处理的 CORS 标头
     */
    public function extractHeaders()
    {
        $headers = [];
        foreach (array_keys($this->cors) as $headerField) {
            $serverField = $this->headerizeToPhp($headerField);
            $headerData = isset($_SERVER[$serverField]) ? $_SERVER[$serverField] : null;
            if ($headerData !== null) {
                $headers[$headerField] = $headerData;
            }
        }

        return $headers;
    }

    /**
     * 对于每个 CORS 头创建特定的响应。
     * @param array $requestHeaders 我们检测到的 CORS headers
     * @return array CORS headers 准备发送
     */
    public function prepareHeaders($requestHeaders)
    {
        $responseHeaders = [];
        // handle Origin
        if (isset($requestHeaders['Origin'], $this->cors['Origin'])) {
            if (in_array($requestHeaders['Origin'], $this->cors['Origin'], true)) {
                $responseHeaders['Access-Control-Allow-Origin'] = $requestHeaders['Origin'];
            }

            if (in_array('*', $this->cors['Origin'], true)) {
                // Per CORS standard (https://fetch.spec.whatwg.org), wildcard origins shouldn't be used together with credentials
                if (isset($this->cors['Access-Control-Allow-Credentials']) && $this->cors['Access-Control-Allow-Credentials']) {
                    if (YII_DEBUG) {
                        throw new InvalidConfigException("Allowing credentials for wildcard origins is insecure. Please specify more restrictive origins or set 'credentials' to false in your CORS configuration.");
                    } else {
                        Yii::error("Allowing credentials for wildcard origins is insecure. Please specify more restrictive origins or set 'credentials' to false in your CORS configuration.", __METHOD__);
                    }
                } else {
                    $responseHeaders['Access-Control-Allow-Origin'] = '*';
                }
            }
        }

        $this->prepareAllowHeaders('Headers', $requestHeaders, $responseHeaders);

        if (isset($requestHeaders['Access-Control-Request-Method'])) {
            $responseHeaders['Access-Control-Allow-Methods'] = implode(', ', $this->cors['Access-Control-Request-Method']);
        }

        if (isset($this->cors['Access-Control-Allow-Credentials'])) {
            $responseHeaders['Access-Control-Allow-Credentials'] = $this->cors['Access-Control-Allow-Credentials'] ? 'true' : 'false';
        }

        if (isset($this->cors['Access-Control-Max-Age']) && $this->request->getIsOptions()) {
            $responseHeaders['Access-Control-Max-Age'] = $this->cors['Access-Control-Max-Age'];
        }

        if (isset($this->cors['Access-Control-Expose-Headers'])) {
            $responseHeaders['Access-Control-Expose-Headers'] = implode(', ', $this->cors['Access-Control-Expose-Headers']);
        }

        return $responseHeaders;
    }

    /**
     * 处理经典 CORS 请求以避免重复代码。
     * @param string $type 我们将处理的头部类型
     * @param array $requestHeaders 客户端请求 CORS 标头
     * @param array $responseHeaders 发送到客户端的 CORS 响应标头
     */
    protected function prepareAllowHeaders($type, $requestHeaders, &$responseHeaders)
    {
        $requestHeaderField = 'Access-Control-Request-' . $type;
        $responseHeaderField = 'Access-Control-Allow-' . $type;
        if (!isset($requestHeaders[$requestHeaderField], $this->cors[$requestHeaderField])) {
            return;
        }
        if (in_array('*', $this->cors[$requestHeaderField])) {
            $responseHeaders[$responseHeaderField] = $this->headerize($requestHeaders[$requestHeaderField]);
        } else {
            $requestedData = preg_split('/[\\s,]+/', $requestHeaders[$requestHeaderField], -1, PREG_SPLIT_NO_EMPTY);
            $acceptedData = array_uintersect($requestedData, $this->cors[$requestHeaderField], 'strcasecmp');
            if (!empty($acceptedData)) {
                $responseHeaders[$responseHeaderField] = implode(', ', $acceptedData);
            }
        }
    }

    /**
     * 将 CORS 标头添加到响应中。
     * @param Response $response
     * @param array $headers  已计算的 CORS 标头
     */
    public function addCorsHeaders($response, $headers)
    {
        if (empty($headers) === false) {
            $responseHeaders = $response->getHeaders();
            foreach ($headers as $field => $value) {
                $responseHeaders->set($field, $value);
            }
        }
    }

    /**
     * 将任何字符串（包括带 HTTP 前缀的 php headers）转换为头格式。
     *
     * 例如：
     *  - X-PINGOTHER -> X-Pingother
     *  - X_PINGOTHER -> X-Pingother
     * @param string $string 要转换的字符串
     * @return string 结果是 "header" 格式
     */
    protected function headerize($string)
    {
        $headers = preg_split('/[\\s,]+/', $string, -1, PREG_SPLIT_NO_EMPTY);
        $headers = array_map(function ($element) {
            return str_replace(' ', '-', ucwords(strtolower(str_replace(['_', '-'], [' ', ' '], $element))));
        }, $headers);
        return implode(', ', $headers);
    }

    /**
     * 将任何字符串（包括带 HTTP 前缀的 php headers）转换为头格式。
     *
     * 例如：
     *  - X-Pingother -> HTTP_X_PINGOTHER
     *  - X PINGOTHER -> HTTP_X_PINGOTHER
     * @param string $string 要转换的字符串
     * @return string 结果是 "php $_SERVER header" 格式
     */
    protected function headerizeToPhp($string)
    {
        return 'HTTP_' . strtoupper(str_replace([' ', '-'], ['_', '_'], $string));
    }
}
