<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\http\Cookie;
use yii\http\CookieCollection;
use yii\http\FileStream;
use yii\http\MemoryStream;
use yii\http\MessageTrait;
use yii\http\UploadedFile;
use yii\http\Uri;
use yii\validators\IpValidator;

/**
 * The web Request class represents an HTTP request.
 *
 * It encapsulates the $_SERVER variable and resolves its inconsistency among different Web servers.
 * Also it provides an interface to retrieve request parameters from $_POST, $_GET, $_COOKIES and REST
 * parameters sent via other HTTP methods like PUT or DELETE.
 *
 * Request is configured as an application component in [[\yii\web\Application]] by default.
 * You can access that instance via `Yii::$app->request`.
 *
 * For more details and usage information on Request, see the [guide article on requests](guide:runtime-requests).
 *
 * @property string $absoluteUrl The currently requested absolute URL. This property is read-only.
 * @property array $acceptableContentTypes The content types ordered by the quality score. Types with the
 * highest scores will be returned first. The array keys are the content types, while the array values are the
 * corresponding quality score and other parameters as given in the header.
 * @property array $acceptableLanguages The languages ordered by the preference level. The first element
 * represents the most preferred language.
 * @property string|null $authPassword The password sent via HTTP authentication, null if the password is not
 * given. This property is read-only.
 * @property string|null $authUser The username sent via HTTP authentication, null if the username is not
 * given. This property is read-only.
 * @property string $baseUrl The relative URL for the application.
 * @property array $parsedBody The request parameters given in the request body.
 * @property string $contentType Request content-type. Null is returned if this information is not available.
 * This property is read-only.
 * @property CookieCollection $cookies The cookie collection. This property is read-only.
 * @property string $csrfToken The token used to perform CSRF validation. This property is read-only.
 * @property string $csrfTokenFromHeader The CSRF token sent via [[CSRF_HEADER]] by browser. Null is returned
 * if no such header is sent. This property is read-only.
 * @property array $eTags The entity tags. This property is read-only.
 * @property string|null $hostInfo Schema and hostname part (with port number if needed) of the request URL
 * (e.g. `http://www.yiiframework.com`), null if can't be obtained from `$_SERVER` and wasn't set. See
 * [[getHostInfo()]] for security related notes on this property.
 * @property string|null $hostName Hostname part of the request URL (e.g. `www.yiiframework.com`). This
 * property is read-only.
 * @property bool $isAjax Whether this is an AJAX (XMLHttpRequest) request. This property is read-only.
 * @property bool $isDelete Whether this is a DELETE request. This property is read-only.
 * @property bool $isFlash Whether this is an Adobe Flash or Adobe Flex request. This property is read-only.
 * @property bool $isGet Whether this is a GET request. This property is read-only.
 * @property bool $isHead Whether this is a HEAD request. This property is read-only.
 * @property bool $isOptions Whether this is a OPTIONS request. This property is read-only.
 * @property bool $isPatch Whether this is a PATCH request. This property is read-only.
 * @property bool $isPost Whether this is a POST request. This property is read-only.
 * @property bool $isPut Whether this is a PUT request. This property is read-only.
 * @property bool $isSecureConnection If the request is sent via secure channel (https). This property is
 * read-only.
 * @property string $method Request method, such as GET, POST, HEAD, PUT, PATCH, DELETE. The value returned is
 * turned into upper case.
 * @property UriInterface $uri the URI instance.
 * @property mixed $requestTarget the message's request target.
 * @property string $pathInfo Part of the request URL that is after the entry script and before the question
 * mark. Note, the returned path info is already URL-decoded.
 * @property int $port Port number for insecure requests.
 * @property array $queryParams The request GET parameter values.
 * @property string $queryString Part of the request URL that is after the question mark. This property is
 * read-only.
 * @property string $rawBody The request body.
 * @property string|null $referrer URL referrer, null if not available. This property is read-only.
 * @property string|null $origin URL origin, null if not available. This property is read-only.
 * @property string $scriptFile The entry script file path.
 * @property string $scriptUrl The relative URL of the entry script.
 * @property int $securePort Port number for secure requests.
 * @property string $serverName Server name, null if not available. This property is read-only.
 * @property int|null $serverPort Server port number, null if not available. This property is read-only.
 * @property string $url The currently requested relative URL. Note that the URI returned may be URL-encoded
 * depending on the client.
 * @property array $uploadedFiles Uploaded files for this request. See [[getUploadedFiles()]] for details.
 * @property string|null $userAgent User agent, null if not available. This property is read-only.
 * @property string|null $userHost User host name, null if not available. This property is read-only.
 * @property string|null $userIP User IP address, null if not available. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 * @SuppressWarnings(PHPMD.SuperGlobals)
 */
class Request extends \yii\base\Request implements ServerRequestInterface
{
    use MessageTrait;

    /**
     * The name of the HTTP header for sending CSRF token.
     */
    const CSRF_HEADER = 'X-CSRF-Token';

    /**
     * @var bool whether to enable CSRF (Cross-Site Request Forgery) validation. Defaults to true.
     * When CSRF validation is enabled, forms submitted to an Yii Web application must be originated
     * from the same application. If not, a 400 HTTP exception will be raised.
     *
     * Note, this feature requires that the user client accepts cookie. Also, to use this feature,
     * forms submitted via POST method must contain a hidden input whose name is specified by [[csrfParam]].
     * You may use [[\yii\helpers\Html::beginForm()]] to generate his hidden input.
     *
     * In JavaScript, you may get the values of [[csrfParam]] and [[csrfToken]] via `yii.getCsrfParam()` and
     * `yii.getCsrfToken()`, respectively. The [[\yii\web\YiiAsset]] asset must be registered.
     * You also need to include CSRF meta tags in your pages by using [[\yii\helpers\Html::csrfMetaTags()]].
     *
     * @see Controller::enableCsrfValidation
     * @see http://en.wikipedia.org/wiki/Cross-site_request_forgery
     */
    public $enableCsrfValidation = true;
    /**
     * @var string the name of the token used to prevent CSRF. Defaults to '_csrf'.
     * This property is used only when [[enableCsrfValidation]] is true.
     */
    public $csrfParam = '_csrf';
    /**
     * @var array the configuration for creating the CSRF [[Cookie|cookie]]. This property is used only when
     * both [[enableCsrfValidation]] and [[enableCsrfCookie]] are true.
     */
    public $csrfCookie = ['httpOnly' => true];
    /**
     * @var bool whether to use cookie to persist CSRF token. If false, CSRF token will be stored
     * in session under the name of [[csrfParam]]. Note that while storing CSRF tokens in session increases
     * security, it requires starting a session for every page, which will degrade your site performance.
     */
    public $enableCsrfCookie = true;
    /**
     * @var bool whether cookies should be validated to ensure they are not tampered. Defaults to true.
     */
    public $enableCookieValidation = true;
    /**
     * @var string a secret key used for cookie validation. This property must be set if [[enableCookieValidation]] is true.
     */
    public $cookieValidationKey;
    /**
     * @var string the name of the POST parameter that is used to indicate if a request is a PUT, PATCH or DELETE
     * request tunneled through POST. Defaults to '_method'.
     * @see getMethod()
     * @see getParsedBody()
     */
    public $methodParam = '_method';
    /**
     * @var array the parsers for converting the raw HTTP request body into [[bodyParams]].
     * The array keys are the request `Content-Types`, and the array values are the
     * corresponding configurations for [[Yii::createObject|creating the parser objects]].
     * A parser must implement the [[RequestParserInterface]].
     *
     * To enable parsing for JSON requests you can use the [[JsonParser]] class like in the following example:
     *
     * ```
     * [
     *     'application/json' => \yii\web\JsonParser::class,
     * ]
     * ```
     *
     * To register a parser for parsing all request types you can use `'*'` as the array key.
     * This one will be used as a fallback in case no other types match.
     *
     * @see getParsedBody()
     */
    public $parsers = [];
    /**
     * @var string name of the class to be used for uploaded file instantiation.
     * This class should implement [[UploadedFileInterface]].
     * @since 3.0.0
     */
    public $uploadedFileClass = UploadedFile::class;
    /**
     * @var array the configuration for trusted security related headers.
     *
     * An array key is an IPv4 or IPv6 IP address in CIDR notation for matching a client.
     *
     * An array value is a list of headers to trust. These will be matched against
     * [[secureHeaders]] to determine which headers are allowed to be sent by a specified host.
     * The case of the header names must be the same as specified in [[secureHeaders]].
     *
     * For example, to trust all headers listed in [[secureHeaders]] for IP addresses
     * in range `192.168.0.0-192.168.0.254` write the following:
     *
     * ```php
     * [
     *     '192.168.0.0/24',
     * ]
     * ```
     *
     * To trust just the `X-Forwarded-For` header from `10.0.0.1`, use:
     *
     * ```
     * [
     *     '10.0.0.1' => ['X-Forwarded-For']
     * ]
     * ```
     *
     * Default is to trust all headers except those listed in [[secureHeaders]] from all hosts.
     * Matches are tried in order and searching is stopped when IP matches.
     *
     * > Info: Matching is performed using [[IpValidator]].
     *   See [[IpValidator::::setRanges()|IpValidator::setRanges()]]
     *   and [[IpValidator::networks]] for advanced matching.
     *
     * @see $secureHeaders
     * @since 2.0.13
     */
    public $trustedHosts = [];
    /**
     * @var array lists of headers that are, by default, subject to the trusted host configuration.
     * These headers will be filtered unless explicitly allowed in [[trustedHosts]].
     * The match of header names is case-insensitive.
     * @see https://en.wikipedia.org/wiki/List_of_HTTP_header_fields
     * @see $trustedHosts
     * @since 2.0.13
     */
    public $secureHeaders = [
        // Common:
        'X-Forwarded-For',
        'X-Forwarded-Host',
        'X-Forwarded-Proto',

        // Microsoft:
        'Front-End-Https',
        'X-Rewrite-Url',
    ];
    /**
     * @var string[] List of headers where proxies store the real client IP.
     * It's not advisable to put insecure headers here.
     * The match of header names is case-insensitive.
     * @see $trustedHosts
     * @see $secureHeaders
     * @since 2.0.13
     */
    public $ipHeaders = [
        'X-Forwarded-For', // Common
    ];
    /**
     * @var array list of headers to check for determining whether the connection is made via HTTPS.
     * The array keys are header names and the array value is a list of header values that indicate a secure connection.
     * The match of header names and values is case-insensitive.
     * It's not advisable to put insecure headers here.
     * @see $trustedHosts
     * @see $secureHeaders
     * @since 2.0.13
     */
    public $secureProtocolHeaders = [
        'X-Forwarded-Proto' => ['https'], // Common
        'Front-End-Https' => ['on'], // Microsoft
    ];

    /**
     * @var array attributes derived from the request.
     * @since 3.0.0
     */
    private $_attributes;
    /**
     * @var array server parameters.
     * @since 3.0.0
     */
    private $_serverParams;
    /**
     * @var array the cookies sent by the client to the server.
     * @since 3.0.0
     */
    private $_cookieParams;
    /**
     * @var CookieCollection Collection of request cookies.
     */
    private $_cookies;
    /**
     * @var string the HTTP method of the request.
     */
    private $_method;
    /**
     * @var UriInterface the URI instance associated with request.
     */
    private $_uri;
    /**
     * @var mixed the message's request target.
     */
    private $_requestTarget;
    /**
     * @var array uploaded files.
     * @since 3.0.0
     */
    private $_uploadedFiles;


    /**
     * Resolves the current request into a route and the associated parameters.
     * @return array the first element is the route, and the second is the associated parameters.
     * @throws NotFoundHttpException if the request cannot be resolved.
     */
    public function resolve()
    {
        $result = Yii::$app->getUrlManager()->parseRequest($this);
        if ($result !== false) {
            [$route, $params] = $result;
            if ($this->_queryParams === null) {
                $_GET = $params + $_GET; // preserve numeric keys
            } else {
                $this->_queryParams = $params + $this->_queryParams;
            }

            return [$route, $this->getQueryParams()];
        }

        throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }

    /**
     * Filters headers according to the [[trustedHosts]].
     * @param array $rawHeaders
     * @return array filtered headers
     * @since 2.0.13
     */
    protected function filterHeaders($rawHeaders)
    {
        // do not trust any of the [[secureHeaders]] by default
        $trustedHeaders = [];

        // check if the client is a trusted host
        if (!empty($this->trustedHosts)) {
            $validator = $this->getIpValidator();
            $ip = $this->getRemoteIP();
            foreach ($this->trustedHosts as $cidr => $headers) {
                if (!is_array($headers)) {
                    $cidr = $headers;
                    $headers = $this->secureHeaders;
                }
                $validator->setRanges($cidr);
                if ($validator->validate($ip)) {
                    $trustedHeaders = $headers;
                    break;
                }
            }
        }

        $rawHeaders = array_change_key_case($rawHeaders, CASE_LOWER);

        // filter all secure headers unless they are trusted
        foreach ($this->secureHeaders as $secureHeader) {
            if (!in_array($secureHeader, $trustedHeaders)) {
                unset($rawHeaders[strtolower($secureHeader)]);
            }
        }

        return $rawHeaders;
    }

    /**
     * Creates instance of [[IpValidator]].
     * You can override this method to adjust validator or implement different matching strategy.
     *
     * @return IpValidator
     * @since 2.0.13
     */
    protected function getIpValidator()
    {
        return new IpValidator();
    }

    /**
     * Returns default message's headers, which should be present once [[headerCollection]] is instantiated.
     * @return string[][] an associative array of the message's headers.
     */
    protected function defaultHeaders()
    {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } elseif (function_exists('http_get_request_headers')) {
            $headers = http_get_request_headers();
        } else {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (strncmp($name, 'HTTP_', 5) === 0) {
                    $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                    $headers[$name] = $value;
                }
            }
        }

        return $this->filterHeaders($headers);
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function getRequestTarget()
    {
        if ($this->_requestTarget === null) {
            $this->_requestTarget = $this->getUri()->__toString();
        }
        return $this->_requestTarget;
    }

    /**
     * Specifies the message's request target
     * @param mixed $requestTarget the message's request target.
     * @since 3.0.0
     */
    public function setRequestTarget($requestTarget)
    {
        $this->_requestTarget = $requestTarget;
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function withRequestTarget($requestTarget)
    {
        if ($this->getRequestTarget() === $requestTarget) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setRequestTarget($requestTarget);
        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        if ($this->_method === null) {
            if (isset($_POST[$this->methodParam])) {
                $this->_method = $_POST[$this->methodParam];
            } elseif ($this->hasHeader('x-http-method-override')) {
                $this->_method = $this->getHeaderLine('x-http-method-override');
            } else {
                $this->_method = $this->getServerParam('REQUEST_METHOD', 'GET');
            }
        }
        return $this->_method;
    }

    /**
     * Specifies request HTTP method.
     * @param string $method case-sensitive HTTP method.
     * @since 3.0.0
     */
    public function setMethod($method)
    {
        $this->_method =  $method;
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function withMethod($method)
    {
        if ($this->getMethod() === $method) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setMethod($method);
        return $newInstance;
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function getUri()
    {
        if (!$this->_uri instanceof UriInterface) {
            if ($this->_uri === null) {
                $uri = new Uri(['string' => $this->getAbsoluteUrl()]);
            } elseif ($this->_uri instanceof \Closure) {
                $uri = call_user_func($this->_uri, $this);
            } else {
                $uri = $this->_uri;
            }

            $this->_uri = Instance::ensure($uri, UriInterface::class);
        }
        return $this->_uri;
    }

    /**
     * Specifies the URI instance.
     * @param UriInterface|\Closure|array $uri URI instance or its DI compatible configuration.
     * @since 3.0.0
     */
    public function setUri($uri)
    {
        $this->_uri = $uri;
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        if ($this->getUri() === $uri) {
            return $this;
        }

        $newInstance = clone $this;

        $newInstance->setUri($uri);
        if (!$preserveHost) {
            return $newInstance->withHeader('host', $uri->getHost());
        }
        return $newInstance;
    }

    /**
     * Returns whether this is a GET request.
     * @return bool whether this is a GET request.
     */
    public function getIsGet()
    {
        return $this->getMethod() === 'GET';
    }

    /**
     * Returns whether this is an OPTIONS request.
     * @return bool whether this is a OPTIONS request.
     */
    public function getIsOptions()
    {
        return $this->getMethod() === 'OPTIONS';
    }

    /**
     * Returns whether this is a HEAD request.
     * @return bool whether this is a HEAD request.
     */
    public function getIsHead()
    {
        return $this->getMethod() === 'HEAD';
    }

    /**
     * Returns whether this is a POST request.
     * @return bool whether this is a POST request.
     */
    public function getIsPost()
    {
        return $this->getMethod() === 'POST';
    }

    /**
     * Returns whether this is a DELETE request.
     * @return bool whether this is a DELETE request.
     */
    public function getIsDelete()
    {
        return $this->getMethod() === 'DELETE';
    }

    /**
     * Returns whether this is a PUT request.
     * @return bool whether this is a PUT request.
     */
    public function getIsPut()
    {
        return $this->getMethod() === 'PUT';
    }

    /**
     * Returns whether this is a PATCH request.
     * @return bool whether this is a PATCH request.
     */
    public function getIsPatch()
    {
        return $this->getMethod() === 'PATCH';
    }

    /**
     * Returns whether this is an AJAX (XMLHttpRequest) request.
     *
     * Note that jQuery doesn't set the header in case of cross domain
     * requests: https://stackoverflow.com/questions/8163703/cross-domain-ajax-doesnt-send-x-requested-with-header
     *
     * @return bool whether this is an AJAX (XMLHttpRequest) request.
     */
    public function getIsAjax()
    {
        return $this->getHeaderLine('x-requested-with') === 'XMLHttpRequest';
    }

    /**
     * Returns whether this is an Adobe Flash or Flex request.
     * @return bool whether this is an Adobe Flash or Adobe Flex request.
     */
    public function getIsFlash()
    {
        $userAgent = $this->getUserAgent();
        if ($userAgent === null) {
            return false;
        }
        return (stripos($userAgent, 'Shockwave') !== false || stripos($userAgent, 'Flash') !== false);
    }

    /**
     * Returns default message body to be used in case it is not explicitly set.
     * @return StreamInterface default body instance.
     */
    protected function defaultBody()
    {
        return new FileStream([
            'filename' => 'php://input',
            'mode' => 'r',
        ]);
    }

    /**
     * Returns the raw HTTP request body.
     * @return string the request body
     */
    public function getRawBody()
    {
        return $this->getBody()->__toString();
    }

    /**
     * Sets the raw HTTP request body, this method is mainly used by test scripts to simulate raw HTTP requests.
     * @param string $rawBody the request body
     */
    public function setRawBody($rawBody)
    {
        $body = new MemoryStream();
        $body->write($rawBody);
        $this->setBody($body);
    }

    private $_parsedBody = false;

    /**
     * Returns the request parameters given in the request body.
     *
     * Request parameters are determined using the parsers configured in [[parsers]] property.
     * If no parsers are configured for the current [[contentType]] it uses the PHP function `mb_parse_str()`
     * to parse the [[rawBody|request body]].
     *
     * Since 2.1.0 body params also include result of [[getUploadedFiles()]].
     *
     * @return array|null the request parameters given in the request body. A `null` value indicates
     * the absence of body content.
     * @throws InvalidConfigException if a registered parser does not implement the [[RequestParserInterface]].
     * @throws UnsupportedMediaTypeHttpException if unable to parse raw body.
     * @see getMethod()
     * @see getParsedBodyParam()
     * @see setParsedBody()
     */
    public function getParsedBody()
    {
        if ($this->_parsedBody === false) {
            if (isset($_POST[$this->methodParam])) {
                $this->_parsedBody = $_POST;
                unset($this->_parsedBody[$this->methodParam]);
                return $this->_parsedBody;
            }

            $contentType = $this->getContentType();
            if (($pos = strpos($contentType, ';')) !== false) {
                // e.g. text/html; charset=UTF-8
                $contentType = trim(substr($contentType, 0, $pos));
            }

            if (isset($this->parsers[$contentType])) {
                $parser = Yii::createObject($this->parsers[$contentType]);
                if (!($parser instanceof RequestParserInterface)) {
                    throw new InvalidConfigException("The '$contentType' request parser is invalid. It must implement the yii\\web\\RequestParserInterface.");
                }
                $this->_parsedBody = $parser->parse($this);
            } elseif (isset($this->parsers['*'])) {
                $parser = Yii::createObject($this->parsers['*']);
                if (!($parser instanceof RequestParserInterface)) {
                    throw new InvalidConfigException('The fallback request parser is invalid. It must implement the yii\\web\\RequestParserInterface.');
                }
                $this->_parsedBody = $parser->parse($this);
            } elseif ($this->getMethod() === 'POST') {
                if ($contentType !== 'application/x-www-form-urlencoded' && $contentType !== 'multipart/form-data') {
                    throw new UnsupportedMediaTypeHttpException();
                }
                // PHP has already parsed the body so we have all params in $_POST
                $this->_parsedBody = $_POST;

                if ($contentType === 'multipart/form-data') {
                    $this->_parsedBody = ArrayHelper::merge($this->_parsedBody, $this->getUploadedFiles());
                }
            } elseif (empty($contentType) && ($this->getBody()->getSize() === 0 || $this->getBody()->getSize() === null)) {
                $this->_parsedBody = null;
            } else {
                if ($contentType !== 'application/x-www-form-urlencoded') {
                    throw new UnsupportedMediaTypeHttpException();
                }
                $this->_parsedBody = [];
                mb_parse_str($this->getBody()->__toString(), $this->_parsedBody);
            }
        }

        return $this->_parsedBody;
    }

    /**
     * Sets the request body parameters.
     * @param array $values the request body parameters (name-value pairs)
     * @see getParsedBodyParam()
     * @see getParsedBody()
     */
    public function setParsedBody($values)
    {
        $this->_parsedBody = $values;
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function withParsedBody($data)
    {
        $newInstance = clone $this;
        $newInstance->setParsedBody($data);
        return $newInstance;
    }

    /**
     * Returns the named request body parameter value.
     * If the parameter does not exist, the second parameter passed to this method will be returned.
     * @param string $name the parameter name
     * @param mixed $defaultValue the default parameter value if the parameter does not exist.
     * @return mixed the parameter value
     * @see getParsedBody()
     * @see setParsedBody()
     */
    public function getParsedBodyParam($name, $defaultValue = null)
    {
        $params = $this->getParsedBody();

        if (is_object($params)) {
            // unable to use `ArrayHelper::getValue()` due to different dots in key logic and lack of exception handling
            try {
                return $params->{$name};
            } catch (\Exception $e) {
                return $defaultValue;
            }
        }

        return $params[$name] ?? $defaultValue;
    }

    /**
     * Returns POST parameter with a given name. If name isn't specified, returns an array of all POST parameters.
     *
     * @param string $name the parameter name
     * @param mixed $defaultValue the default parameter value if the parameter does not exist.
     * @return array|mixed
     */
    public function post($name = null, $defaultValue = null)
    {
        if ($name === null) {
            return $this->getParsedBody();
        }

        return $this->getParsedBodyParam($name, $defaultValue);
    }

    private $_queryParams;

    /**
     * Returns the request parameters given in the [[queryString]].
     *
     * This method will return the contents of `$_GET` if params where not explicitly set.
     * @return array the request GET parameter values.
     * @see setQueryParams()
     */
    public function getQueryParams()
    {
        if ($this->_queryParams === null) {
            return $_GET;
        }

        return $this->_queryParams;
    }

    /**
     * Sets the request [[queryString]] parameters.
     * @param array $values the request query parameters (name-value pairs)
     * @see getQueryParam()
     * @see getQueryParams()
     */
    public function setQueryParams($values)
    {
        $this->_queryParams = $values;
    }

    /**
     * {@inheritdoc}
     */
    public function withQueryParams(array $query)
    {
        if ($this->getQueryParams() === $query) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setQueryParams($query);
        return $newInstance;
    }

    /**
     * Returns GET parameter with a given name. If name isn't specified, returns an array of all GET parameters.
     *
     * @param string $name the parameter name
     * @param mixed $defaultValue the default parameter value if the parameter does not exist.
     * @return array|mixed
     */
    public function get($name = null, $defaultValue = null)
    {
        if ($name === null) {
            return $this->getQueryParams();
        }

        return $this->getQueryParam($name, $defaultValue);
    }

    /**
     * Returns the named GET parameter value.
     * If the GET parameter does not exist, the second parameter passed to this method will be returned.
     * @param string $name the GET parameter name.
     * @param mixed $defaultValue the default parameter value if the GET parameter does not exist.
     * @return mixed the GET parameter value
     * @see getParsedBodyParam()
     */
    public function getQueryParam($name, $defaultValue = null)
    {
        $params = $this->getQueryParams();

        return $params[$name] ?? $defaultValue;
    }

    /**
     * Sets the data related to the incoming request environment.
     * @param array $serverParams server parameters.
     * @since 3.0.0
     */
    public function setServerParams(array $serverParams)
    {
        $this->_serverParams = $serverParams;
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function getServerParams()
    {
        if ($this->_serverParams === null) {
            $this->_serverParams = $_SERVER;
        }
        return $this->_serverParams;
    }

    /**
     * Return the server environment parameter by name.
     * @param string $name parameter name.
     * @param mixed $default default value to return if the parameter does not exist.
     * @return mixed parameter value.
     * @since 3.0.0
     */
    public function getServerParam($name, $default = null)
    {
        $params = $this->getServerParams();
        if (!isset($params[$name])) {
            return $default;
        }
        return $params[$name];
    }

    /**
     * Specifies cookies.
     * @param array $cookies array of key/value pairs representing cookies.
     * @since 3.0.0
     */
    public function setCookieParams(array $cookies)
    {
        $this->_cookieParams = $cookies;
        $this->_cookies = null;
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function getCookieParams()
    {
        if ($this->_cookieParams === null) {
            $this->_cookieParams = $_COOKIE;
        }
        return $this->_cookieParams;
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function withCookieParams(array $cookies)
    {
        if ($this->getCookieParams() === $cookies) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setCookieParams($cookies);
        return $newInstance;
    }

    private $_hostInfo;
    private $_hostName;

    /**
     * Returns the schema and host part of the current request URL.
     *
     * The returned URL does not have an ending slash.
     *
     * By default this value is based on the user request information. This method will
     * return the value of `$_SERVER['HTTP_HOST']` if it is available or `$_SERVER['SERVER_NAME']` if not.
     * You may want to check out the [PHP documentation](http://php.net/manual/en/reserved.variables.server.php)
     * for more information on these variables.
     *
     * You may explicitly specify it by setting the [[setHostInfo()|hostInfo]] property.
     *
     * > Warning: Dependent on the server configuration this information may not be
     * > reliable and [may be faked by the user sending the HTTP request](https://www.acunetix.com/vulnerabilities/web/host-header-attack).
     * > If the webserver is configured to serve the same site independent of the value of
     * > the `Host` header, this value is not reliable. In such situations you should either
     * > fix your webserver configuration or explicitly set the value by setting the [[setHostInfo()|hostInfo]] property.
     * > If you don't have access to the server configuration, you can setup [[\yii\filters\HostControl]] filter at
     * > application level in order to protect against such kind of attack.
     *
     * @property string|null schema and hostname part (with port number if needed) of the request URL
     * (e.g. `http://www.yiiframework.com`), null if can't be obtained from `$_SERVER` and wasn't set.
     * See [[getHostInfo()]] for security related notes on this property.
     * @return string|null schema and hostname part (with port number if needed) of the request URL
     * (e.g. `http://www.yiiframework.com`), null if can't be obtained from `$_SERVER` and wasn't set.
     * @see setHostInfo()
     */
    public function getHostInfo()
    {
        if ($this->_hostInfo === null) {
            $secure = $this->getIsSecureConnection();
            $http = $secure ? 'https' : 'http';

            if ($this->hasHeader('X-Forwarded-Host')) {
                $this->_hostInfo = $http . '://' . trim(explode(',', $this->getHeaderLine('X-Forwarded-Host'))[0]);
            } elseif ($this->hasHeader('Host')) {
                $this->_hostInfo = $http . '://' . $this->getHeaderLine('Host');
            } elseif (($serverName = $this->getServerParam('SERVER_NAME')) !== null) {
                $this->_hostInfo = $http . '://' . $serverName;
                $port = $secure ? $this->getSecurePort() : $this->getPort();
                if (($port !== 80 && !$secure) || ($port !== 443 && $secure)) {
                    $this->_hostInfo .= ':' . $port;
                }
            }
        }

        return $this->_hostInfo;
    }

    /**
     * Sets the schema and host part of the application URL.
     * This setter is provided in case the schema and hostname cannot be determined
     * on certain Web servers.
     * @param string|null $value the schema and host part of the application URL. The trailing slashes will be removed.
     * @see getHostInfo() for security related notes on this property.
     */
    public function setHostInfo($value)
    {
        $this->_hostName = null;
        $this->_hostInfo = $value === null ? null : rtrim($value, '/');
    }

    /**
     * Returns the host part of the current request URL.
     * Value is calculated from current [[getHostInfo()|hostInfo]] property.
     *
     * > Warning: The content of this value may not be reliable, dependent on the server
     * > configuration. Please refer to [[getHostInfo()]] for more information.
     *
     * @return string|null hostname part of the request URL (e.g. `www.yiiframework.com`)
     * @see getHostInfo()
     * @since 2.0.10
     */
    public function getHostName()
    {
        if ($this->_hostName === null) {
            $this->_hostName = parse_url($this->getHostInfo(), PHP_URL_HOST);
        }

        return $this->_hostName;
    }

    private $_baseUrl;

    /**
     * Returns the relative URL for the application.
     * This is similar to [[scriptUrl]] except that it does not include the script file name,
     * and the ending slashes are removed.
     * @return string the relative URL for the application
     * @see setScriptUrl()
     */
    public function getBaseUrl()
    {
        if ($this->_baseUrl === null) {
            $this->_baseUrl = rtrim(dirname($this->getScriptUrl()), '\\/');
        }

        return $this->_baseUrl;
    }

    /**
     * Sets the relative URL for the application.
     * By default the URL is determined based on the entry script URL.
     * This setter is provided in case you want to change this behavior.
     * @param string $value the relative URL for the application
     */
    public function setBaseUrl($value)
    {
        $this->_baseUrl = $value;
    }

    private $_scriptUrl;

    /**
     * Returns the relative URL of the entry script.
     * The implementation of this method referenced Zend_Controller_Request_Http in Zend Framework.
     * @return string the relative URL of the entry script.
     * @throws InvalidConfigException if unable to determine the entry script URL
     */
    public function getScriptUrl()
    {
        if ($this->_scriptUrl === null) {
            $scriptFile = $this->getScriptFile();
            $scriptName = basename($scriptFile);
            $serverParams = $this->getServerParams();
            if (isset($serverParams['SCRIPT_NAME']) && basename($serverParams['SCRIPT_NAME']) === $scriptName) {
                $this->_scriptUrl = $serverParams['SCRIPT_NAME'];
            } elseif (isset($serverParams['PHP_SELF']) && basename($serverParams['PHP_SELF']) === $scriptName) {
                $this->_scriptUrl = $serverParams['PHP_SELF'];
            } elseif (isset($serverParams['ORIG_SCRIPT_NAME']) && basename($serverParams['ORIG_SCRIPT_NAME']) === $scriptName) {
                $this->_scriptUrl = $serverParams['ORIG_SCRIPT_NAME'];
            } elseif (isset($serverParams['PHP_SELF']) && ($pos = strpos($serverParams['PHP_SELF'], '/' . $scriptName)) !== false) {
                $this->_scriptUrl = substr($serverParams['SCRIPT_NAME'], 0, $pos) . '/' . $scriptName;
            } elseif (!empty($serverParams['DOCUMENT_ROOT']) && strpos($scriptFile, $serverParams['DOCUMENT_ROOT']) === 0) {
                $this->_scriptUrl = str_replace([$serverParams['DOCUMENT_ROOT'], '\\'], ['', '/'], $scriptFile);
            } else {
                throw new InvalidConfigException('Unable to determine the entry script URL.');
            }
        }

        return $this->_scriptUrl;
    }

    /**
     * Sets the relative URL for the application entry script.
     * This setter is provided in case the entry script URL cannot be determined
     * on certain Web servers.
     * @param string $value the relative URL for the application entry script.
     */
    public function setScriptUrl($value)
    {
        $this->_scriptUrl = $value === null ? null : '/' . trim($value, '/');
    }

    private $_scriptFile;

    /**
     * Returns the entry script file path.
     * The default implementation will simply return `$_SERVER['SCRIPT_FILENAME']`.
     * @return string the entry script file path
     * @throws InvalidConfigException
     */
    public function getScriptFile()
    {
        if (isset($this->_scriptFile)) {
            return $this->_scriptFile;
        }

        if (($scriptFilename = $this->getServerParam('SCRIPT_FILENAME')) !== null) {
            return $scriptFilename;
        }

        throw new InvalidConfigException('Unable to determine the entry script file path.');
    }

    /**
     * Sets the entry script file path.
     * The entry script file path normally can be obtained from `$_SERVER['SCRIPT_FILENAME']`.
     * If your server configuration does not return the correct value, you may configure
     * this property to make it right.
     * @param string $value the entry script file path.
     */
    public function setScriptFile($value)
    {
        $this->_scriptFile = $value;
    }

    private $_pathInfo;

    /**
     * Returns the path info of the currently requested URL.
     * A path info refers to the part that is after the entry script and before the question mark (query string).
     * The starting and ending slashes are both removed.
     * @return string part of the request URL that is after the entry script and before the question mark.
     * Note, the returned path info is already URL-decoded.
     * @throws InvalidConfigException if the path info cannot be determined due to unexpected server configuration
     */
    public function getPathInfo()
    {
        if ($this->_pathInfo === null) {
            $this->_pathInfo = $this->resolvePathInfo();
        }

        return $this->_pathInfo;
    }

    /**
     * Sets the path info of the current request.
     * This method is mainly provided for testing purpose.
     * @param string $value the path info of the current request
     */
    public function setPathInfo($value)
    {
        $this->_pathInfo = $value === null ? null : ltrim($value, '/');
    }

    /**
     * Resolves the path info part of the currently requested URL.
     * A path info refers to the part that is after the entry script and before the question mark (query string).
     * The starting slashes are both removed (ending slashes will be kept).
     * @return string part of the request URL that is after the entry script and before the question mark.
     * Note, the returned path info is decoded.
     * @throws InvalidConfigException if the path info cannot be determined due to unexpected server configuration
     */
    protected function resolvePathInfo()
    {
        $pathInfo = $this->getUrl();

        if (($pos = strpos($pathInfo, '?')) !== false) {
            $pathInfo = substr($pathInfo, 0, $pos);
        }

        $pathInfo = urldecode($pathInfo);

        // try to encode in UTF8 if not so
        // http://w3.org/International/questions/qa-forms-utf-8.html
        if (!preg_match('%^(?:
            [\x09\x0A\x0D\x20-\x7E]              # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
            | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
            )*$%xs', $pathInfo)
        ) {
            $pathInfo = utf8_encode($pathInfo);
        }

        $scriptUrl = $this->getScriptUrl();
        $baseUrl = $this->getBaseUrl();
        if (strpos($pathInfo, $scriptUrl) === 0) {
            $pathInfo = substr($pathInfo, strlen($scriptUrl));
        } elseif ($baseUrl === '' || strpos($pathInfo, $baseUrl) === 0) {
            $pathInfo = substr($pathInfo, strlen($baseUrl));
        } elseif (($phpSelf = $this->getServerParam('PHP_SELF')) !== null && strpos($phpSelf, $scriptUrl) === 0) {
            $pathInfo = substr($phpSelf, strlen($scriptUrl));
        } else {
            throw new InvalidConfigException('Unable to determine the path info of the current request.');
        }

        if (substr($pathInfo, 0, 1) === '/') {
            $pathInfo = substr($pathInfo, 1);
        }

        return (string) $pathInfo;
    }

    /**
     * Returns the currently requested absolute URL.
     * This is a shortcut to the concatenation of [[hostInfo]] and [[url]].
     * @return string the currently requested absolute URL.
     */
    public function getAbsoluteUrl()
    {
        return $this->getHostInfo() . $this->getUrl();
    }

    private $_url;

    /**
     * Returns the currently requested relative URL.
     * This refers to the portion of the URL that is after the [[hostInfo]] part.
     * It includes the [[queryString]] part if any.
     * @return string the currently requested relative URL. Note that the URI returned may be URL-encoded depending on the client.
     * @throws InvalidConfigException if the URL cannot be determined due to unusual server configuration
     */
    public function getUrl()
    {
        if ($this->_url === null) {
            $this->_url = $this->resolveRequestUri();
        }

        return $this->_url;
    }

    /**
     * Sets the currently requested relative URL.
     * The URI must refer to the portion that is after [[hostInfo]].
     * Note that the URI should be URL-encoded.
     * @param string $value the request URI to be set
     */
    public function setUrl($value)
    {
        $this->_url = $value;
    }

    /**
     * Resolves the request URI portion for the currently requested URL.
     * This refers to the portion that is after the [[hostInfo]] part. It includes the [[queryString]] part if any.
     * The implementation of this method referenced Zend_Controller_Request_Http in Zend Framework.
     * @return string|bool the request URI portion for the currently requested URL.
     * Note that the URI returned may be URL-encoded depending on the client.
     * @throws InvalidConfigException if the request URI cannot be determined due to unusual server configuration
     */
    protected function resolveRequestUri()
    {
        $serverParams = $this->getServerParams();

        if ($this->hasHeader('x-rewrite-url')) { // IIS
            $requestUri = $this->getHeaderLine('x-rewrite-url');
        } elseif (isset($serverParams['REQUEST_URI'])) {
            $requestUri = $serverParams['REQUEST_URI'];
            if ($requestUri !== '' && $requestUri[0] !== '/') {
                $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
            }
        } elseif (isset($serverParams['ORIG_PATH_INFO'])) { // IIS 5.0 CGI
            $requestUri = $serverParams['ORIG_PATH_INFO'];
            if (!empty($serverParams['QUERY_STRING'])) {
                $requestUri .= '?' . $serverParams['QUERY_STRING'];
            }
        } else {
            throw new InvalidConfigException('Unable to determine the request URI.');
        }

        return $requestUri;
    }

    /**
     * Returns part of the request URL that is after the question mark.
     * @return string part of the request URL that is after the question mark
     */
    public function getQueryString()
    {
        return $this->getServerParam('QUERY_STRING', '');
    }

    /**
     * Return if the request is sent via secure channel (https).
     * @return bool if the request is sent via secure channel (https)
     */
    public function getIsSecureConnection()
    {
        $https = $this->getServerParam('HTTPS');
        if ($https !== null && (strcasecmp($https, 'on') === 0 || $https == 1)) {
            return true;
        }
        foreach ($this->secureProtocolHeaders as $header => $values) {
            if ($this->hasHeader($header)) {
                foreach ($values as $value) {
                    if (strcasecmp($this->getHeaderLine($header), $value) === 0) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Returns the server name.
     * @return string server name, null if not available
     */
    public function getServerName()
    {
        return $this->getServerParam('SERVER_NAME');
    }

    /**
     * Returns the server port number.
     * @return int|null server port number, null if not available
     */
    public function getServerPort()
    {
        $port = $this->getServerParam('SERVER_PORT');
        return $port === null ? null : (int) $port;
    }

    /**
     * Returns the URL referrer.
     * @return string|null URL referrer, null if not available
     */
    public function getReferrer()
    {
        if (!$this->hasHeader('Referer')) {
            return null;
        }
        return $this->getHeaderLine('Referer');
    }

    /**
     * Returns the URL origin of a CORS request.
     *
     * The return value is taken from the `Origin` [[getHeaders()|header]] sent by the browser.
     *
     * Note that the origin request header indicates where a fetch originates from.
     * It doesn't include any path information, but only the server name.
     * It is sent with a CORS requests, as well as with POST requests.
     * It is similar to the referer header, but, unlike this header, it doesn't disclose the whole path.
     * Please refer to <https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Origin> for more information.
     *
     * @return string|null URL origin of a CORS request, `null` if not available.
     * @see getHeaders()
     * @since 2.0.13
     */
    public function getOrigin()
    {
        return $this->getHeaderLine('origin');
    }

    /**
     * Returns the user agent.
     * @return string|null user agent, null if not available
     */
    public function getUserAgent()
    {
        if (!$this->hasHeader('User-Agent')) {
            return null;
        }
        return $this->getHeaderLine('User-Agent');
    }

    /**
     * Returns the user IP address.
     * The IP is determined using headers and / or `$_SERVER` variables.
     * @return string|null user IP address, null if not available
     */
    public function getUserIP()
    {
        foreach ($this->ipHeaders as $ipHeader) {
            if ($this->hasHeader($ipHeader)) {
                return trim(explode(',', $this->getHeaderLine($ipHeader))[0]);
            }
        }

        return $this->getRemoteIP();
    }

    /**
     * Returns the user host name.
     * The HOST is determined using headers and / or `$_SERVER` variables.
     * @return string|null user host name, null if not available
     */
    public function getUserHost()
    {
        foreach ($this->ipHeaders as $ipHeader) {
            if ($this->hasHeader($ipHeader)) {
                return gethostbyaddr(trim(explode(',', $this->getHeaderLine($ipHeader))[0]));
            }
        }

        return $this->getRemoteHost();
    }

    /**
     * Returns the IP on the other end of this connection.
     * This is always the next hop, any headers are ignored.
     * @return string|null remote IP address, `null` if not available.
     * @since 2.0.13
     */
    public function getRemoteIP()
    {
        return $this->getServerParam('REMOTE_ADDR');
    }

    /**
     * Returns the host name of the other end of this connection.
     * This is always the next hop, any headers are ignored.
     * @return string|null remote host name, `null` if not available
     * @see getUserHost()
     * @see getRemoteIP()
     * @since 2.0.13
     */
    public function getRemoteHost()
    {
        return $this->getServerParam('REMOTE_HOST');
    }

    /**
     * @return string|null the username sent via HTTP authentication, `null` if the username is not given
     * @see getAuthCredentials() to get both username and password in one call
     */
    public function getAuthUser()
    {
        return $this->getAuthCredentials()[0];
    }

    /**
     * @return string|null the password sent via HTTP authentication, `null` if the password is not given
     * @see getAuthCredentials() to get both username and password in one call
     */
    public function getAuthPassword()
    {
        return $this->getAuthCredentials()[1];
    }

    /**
     * @return array that contains exactly two elements:
     * - 0: the username sent via HTTP authentication, `null` if the username is not given
     * - 1: the password sent via HTTP authentication, `null` if the password is not given
     * @see getAuthUser() to get only username
     * @see getAuthPassword() to get only password
     * @since 2.0.13
     */
    public function getAuthCredentials()
    {
        $username = $this->getServerParam('PHP_AUTH_USER');
        $password = $this->getServerParam('PHP_AUTH_PW');
        if ($username !== null || $password !== null) {
            return [$username, $password];
        }

        /*
         * Apache with php-cgi does not pass HTTP Basic authentication to PHP by default.
         * To make it work, add the following line to to your .htaccess file:
         *
         * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
         */
        $auth_token = $this->getHeader('HTTP_AUTHORIZATION') ?: $this->getHeader('REDIRECT_HTTP_AUTHORIZATION');
        if ($auth_token !== [] && strncasecmp($auth_token[0], 'basic', 5) === 0) {
            $parts = array_map(function ($value) {
                return strlen($value) === 0 ? null : $value;
            }, explode(':', base64_decode(mb_substr($auth_token[0], 6)), 2));

            if (count($parts) < 2) {
                return [$parts[0], null];
            }

            return $parts;
        }

        return [null, null];
    }

    private $_port;

    /**
     * Returns the port to use for insecure requests.
     * Defaults to 80, or the port specified by the server if the current
     * request is insecure.
     * @return int port number for insecure requests.
     * @see setPort()
     */
    public function getPort()
    {
        if ($this->_port === null) {
            $serverPort = $this->getServerPort();
            $this->_port = !$this->getIsSecureConnection() && $serverPort !== null ? $serverPort : 80;
        }

        return $this->_port;
    }

    /**
     * Sets the port to use for insecure requests.
     * This setter is provided in case a custom port is necessary for certain
     * server configurations.
     * @param int $value port number.
     */
    public function setPort($value)
    {
        if ($value != $this->_port) {
            $this->_port = (int) $value;
            $this->_hostInfo = null;
        }
    }

    private $_securePort;

    /**
     * Returns the port to use for secure requests.
     * Defaults to 443, or the port specified by the server if the current
     * request is secure.
     * @return int port number for secure requests.
     * @see setSecurePort()
     */
    public function getSecurePort()
    {
        if ($this->_securePort === null) {
            $serverPort = $this->getServerPort();
            $this->_securePort = $this->getIsSecureConnection() && $serverPort !== null ? $serverPort : 443;
        }

        return $this->_securePort;
    }

    /**
     * Sets the port to use for secure requests.
     * This setter is provided in case a custom port is necessary for certain
     * server configurations.
     * @param int $value port number.
     */
    public function setSecurePort($value)
    {
        if ($value != $this->_securePort) {
            $this->_securePort = (int) $value;
            $this->_hostInfo = null;
        }
    }

    private $_contentTypes;

    /**
     * Returns the content types acceptable by the end user.
     *
     * This is determined by the `Accept` HTTP header. For example,
     *
     * ```php
     * $_SERVER['HTTP_ACCEPT'] = 'text/plain; q=0.5, application/json; version=1.0, application/xml; version=2.0;';
     * $types = $request->getAcceptableContentTypes();
     * print_r($types);
     * // displays:
     * // [
     * //     'application/json' => ['q' => 1, 'version' => '1.0'],
     * //      'application/xml' => ['q' => 1, 'version' => '2.0'],
     * //           'text/plain' => ['q' => 0.5],
     * // ]
     * ```
     *
     * @return array the content types ordered by the quality score. Types with the highest scores
     * will be returned first. The array keys are the content types, while the array values
     * are the corresponding quality score and other parameters as given in the header.
     */
    public function getAcceptableContentTypes()
    {
        if ($this->_contentTypes === null) {
            if ($this->hasHeader('Accept')) {
                $this->_contentTypes = $this->parseAcceptHeader($this->getHeaderLine('Accept'));
            } else {
                $this->_contentTypes = [];
            }
        }

        return $this->_contentTypes;
    }

    /**
     * Sets the acceptable content types.
     * Please refer to [[getAcceptableContentTypes()]] on the format of the parameter.
     * @param array $value the content types that are acceptable by the end user. They should
     * be ordered by the preference level.
     * @see getAcceptableContentTypes()
     * @see parseAcceptHeader()
     */
    public function setAcceptableContentTypes($value)
    {
        $this->_contentTypes = $value;
    }

    /**
     * Returns request content-type
     * The Content-Type header field indicates the MIME type of the data
     * contained in [[getBody()]] or, in the case of the HEAD method, the
     * media type that would have been sent had the request been a GET.
     * For the MIME-types the user expects in response, see [[acceptableContentTypes]].
     * @return string request content-type. Empty string is returned if this information is not available.
     * @link https://tools.ietf.org/html/rfc2616#section-14.17
     * HTTP 1.1 header field definitions
     */
    public function getContentType()
    {
        return $this->getHeaderLine('Content-Type');
    }

    private $_languages;

    /**
     * Returns the languages acceptable by the end user.
     * This is determined by the `Accept-Language` HTTP header.
     * @return array the languages ordered by the preference level. The first element
     * represents the most preferred language.
     */
    public function getAcceptableLanguages()
    {
        if ($this->_languages === null) {
            if ($this->hasHeader('Accept-Language')) {
                $this->_languages = array_keys($this->parseAcceptHeader($this->getHeaderLine('Accept-Language')));
            } else {
                $this->_languages = [];
            }
        }

        return $this->_languages;
    }

    /**
     * @param array $value the languages that are acceptable by the end user. They should
     * be ordered by the preference level.
     */
    public function setAcceptableLanguages($value)
    {
        $this->_languages = $value;
    }

    /**
     * Parses the given `Accept` (or `Accept-Language`) header.
     *
     * This method will return the acceptable values with their quality scores and the corresponding parameters
     * as specified in the given `Accept` header. The array keys of the return value are the acceptable values,
     * while the array values consisting of the corresponding quality scores and parameters. The acceptable
     * values with the highest quality scores will be returned first. For example,
     *
     * ```php
     * $header = 'text/plain; q=0.5, application/json; version=1.0, application/xml; version=2.0;';
     * $accepts = $request->parseAcceptHeader($header);
     * print_r($accepts);
     * // displays:
     * // [
     * //     'application/json' => ['q' => 1, 'version' => '1.0'],
     * //      'application/xml' => ['q' => 1, 'version' => '2.0'],
     * //           'text/plain' => ['q' => 0.5],
     * // ]
     * ```
     *
     * @param string $header the header to be parsed
     * @return array the acceptable values ordered by their quality score. The values with the highest scores
     * will be returned first.
     */
    public function parseAcceptHeader($header)
    {
        $accepts = [];
        foreach (explode(',', $header) as $i => $part) {
            $params = preg_split('/\s*;\s*/', trim($part), -1, PREG_SPLIT_NO_EMPTY);
            if (empty($params)) {
                continue;
            }
            $values = [
                'q' => [$i, array_shift($params), 1],
            ];
            foreach ($params as $param) {
                if (strpos($param, '=') !== false) {
                    [$key, $value] = explode('=', $param, 2);
                    if ($key === 'q') {
                        $values['q'][2] = (float) $value;
                    } else {
                        $values[$key] = $value;
                    }
                } else {
                    $values[] = $param;
                }
            }
            $accepts[] = $values;
        }

        usort($accepts, function ($a, $b) {
            $a = $a['q']; // index, name, q
            $b = $b['q'];
            if ($a[2] > $b[2]) {
                return -1;
            }

            if ($a[2] < $b[2]) {
                return 1;
            }

            if ($a[1] === $b[1]) {
                return $a[0] > $b[0] ? 1 : -1;
            }

            if ($a[1] === '*/*') {
                return 1;
            }

            if ($b[1] === '*/*') {
                return -1;
            }

            $wa = $a[1][strlen($a[1]) - 1] === '*';
            $wb = $b[1][strlen($b[1]) - 1] === '*';
            if ($wa xor $wb) {
                return $wa ? 1 : -1;
            }

            return $a[0] > $b[0] ? 1 : -1;
        });

        $result = [];
        foreach ($accepts as $accept) {
            $name = $accept['q'][1];
            $accept['q'] = $accept['q'][2];
            $result[$name] = $accept;
        }

        return $result;
    }

    /**
     * Returns the user-preferred language that should be used by this application.
     * The language resolution is based on the user preferred languages and the languages
     * supported by the application. The method will try to find the best match.
     * @param array $languages a list of the languages supported by the application. If this is empty, the current
     * application language will be returned without further processing.
     * @return string the language that the application should use.
     */
    public function getPreferredLanguage(array $languages = [])
    {
        if (empty($languages)) {
            return Yii::$app->language;
        }
        foreach ($this->getAcceptableLanguages() as $acceptableLanguage) {
            $acceptableLanguage = str_replace('_', '-', strtolower($acceptableLanguage));
            foreach ($languages as $language) {
                $normalizedLanguage = str_replace('_', '-', strtolower($language));

                if (
                    $normalizedLanguage === $acceptableLanguage // en-us==en-us
                    || strpos($acceptableLanguage, $normalizedLanguage . '-') === 0 // en==en-us
                    || strpos($normalizedLanguage, $acceptableLanguage . '-') === 0 // en-us==en
                ) {
                    return $language;
                }
            }
        }

        return reset($languages);
    }

    /**
     * Gets the Etags.
     *
     * @return array The entity tags
     */
    public function getETags()
    {
        if ($this->hasHeader('if-none-match')) {
            return preg_split('/[\s,]+/', str_replace('-gzip', '', $this->getHeaderLine('if-none-match')), -1, PREG_SPLIT_NO_EMPTY);
        }

        return [];
    }

    /**
     * Returns the cookie collection.
     *
     * Through the returned cookie collection, you may access a cookie using the following syntax:
     *
     * ```php
     * $cookie = $request->cookies['name']
     * if ($cookie !== null) {
     *     $value = $cookie->value;
     * }
     *
     * // alternatively
     * $value = $request->cookies->getValue('name');
     * ```
     *
     * @return CookieCollection the cookie collection.
     */
    public function getCookies()
    {
        if ($this->_cookies === null) {
            $this->_cookies = new CookieCollection($this->loadCookies(), [
                'readOnly' => true,
            ]);
        }

        return $this->_cookies;
    }

    /**
     * Converts [[cookieParams]] into an array of [[Cookie]].
     * @return array the cookies obtained from request
     * @throws InvalidConfigException if [[cookieValidationKey]] is not set when [[enableCookieValidation]] is true
     */
    protected function loadCookies()
    {
        $cookies = [];
        if ($this->enableCookieValidation) {
            if ($this->cookieValidationKey == '') {
                throw new InvalidConfigException(get_class($this) . '::$cookieValidationKey must be configured with a secret key.');
            }
            foreach ($this->getCookieParams() as $name => $value) {
                if (!is_string($value)) {
                    continue;
                }
                $data = Yii::$app->getSecurity()->validateData($value, $this->cookieValidationKey);
                if ($data === false) {
                    continue;
                }
                $data = @unserialize($data);
                if (is_array($data) && isset($data[0], $data[1]) && $data[0] === $name) {
                    $cookies[$name] = Yii::createObject([
                        '__class' => \yii\http\Cookie::class,
                        'name' => $name,
                        'value' => $data[1],
                        'expire' => null,
                    ]);
                }
            }
        } else {
            foreach ($this->getCookieParams() as $name => $value) {
                $cookies[$name] = Yii::createObject([
                    '__class' => \yii\http\Cookie::class,
                    'name' => $name,
                    'value' => $value,
                    'expire' => null,
                ]);
            }
        }

        return $cookies;
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function getUploadedFiles()
    {
        if ($this->_uploadedFiles === null) {
            $this->getParsedBody(); // uploaded files are the part of the body and may be set while its parsing
            if ($this->_uploadedFiles === null) {
                $this->_uploadedFiles = $this->defaultUploadedFiles();
            }
        }
        return $this->_uploadedFiles;
    }

    /**
     * Sets uploaded files for this request.
     * Data structure for the uploaded files should follow [PSR-7 Uploaded Files specs](http://www.php-fig.org/psr/psr-7/#16-uploaded-files).
     * @param array|null $uploadedFiles uploaded files.
     * @since 3.0.0
     */
    public function setUploadedFiles($uploadedFiles)
    {
        $this->_uploadedFiles = $uploadedFiles;
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $newInstance = clone $this;
        $newInstance->setUploadedFiles($uploadedFiles);
        return $newInstance;
    }

    /**
     * Initializes default uploaded files data structure parsing super-global $_FILES.
     * @see http://www.php-fig.org/psr/psr-7/#16-uploaded-files
     * @return array uploaded files.
     * @since 3.0.0
     */
    protected function defaultUploadedFiles()
    {
        $files = [];
        foreach ($_FILES as $class => $info) {
            $files[$class] = [];
            $this->populateUploadedFileRecursive($files[$class], $info['name'], $info['tmp_name'], $info['type'], $info['size'], $info['error']);
        }

        return $files;
    }

    /**
     * Populates uploaded files array from $_FILE data structure recursively.
     * @param array $files uploaded files array to be populated.
     * @param mixed $names file names provided by PHP
     * @param mixed $tempNames temporary file names provided by PHP
     * @param mixed $types file types provided by PHP
     * @param mixed $sizes file sizes provided by PHP
     * @param mixed $errors uploading issues provided by PHP
     * @since 3.0.0
     */
    private function populateUploadedFileRecursive(&$files, $names, $tempNames, $types, $sizes, $errors)
    {
        if (is_array($names)) {
            foreach ($names as $i => $name) {
                $files[$i] = [];
                $this->populateUploadedFileRecursive($files[$i], $name, $tempNames[$i], $types[$i], $sizes[$i], $errors[$i]);
            }
        } else {
            $files = Yii::createObject([
                '__class' => $this->uploadedFileClass,
                'clientFilename' => $names,
                'tempFilename' => $tempNames,
                'clientMediaType' => $types,
                'size' => $sizes,
                'error' => $errors,
            ]);
        }
    }

    /**
     * Returns an uploaded file according to the given name.
     * Name can be either a string HTML form input name, e.g. 'Item[file]' or array path, e.g. `['Item', 'file']`.
     * Note: this method returns `null` in case given name matches multiple files.
     * @param string|array $name HTML form input name or array path.
     * @return UploadedFileInterface|null uploaded file instance, `null` - if not found.
     * @since 3.0.0
     */
    public function getUploadedFileByName($name)
    {
        $uploadedFile = $this->findUploadedFiles($name);
        if ($uploadedFile instanceof UploadedFileInterface) {
            return $uploadedFile;
        }
        return null;
    }

    /**
     * Returns the list of uploaded file instances according to the given name.
     * Name can be either a string HTML form input name, e.g. 'Item[file]' or array path, e.g. `['Item', 'file']`.
     * Note: this method does NOT preserve uploaded files structure - it returns instances in single-level array (list),
     * even if they are set by nested keys.
     * @param string|array $name HTML form input name or array path.
     * @return UploadedFileInterface[] list of uploaded file instances.
     * @since 3.0.0
     */
    public function getUploadedFilesByName($name)
    {
        $uploadedFiles = $this->findUploadedFiles($name);
        if ($uploadedFiles === null) {
            return [];
        }
        if ($uploadedFiles instanceof UploadedFileInterface) {
            return [$uploadedFiles];
        }
        return $this->reduceUploadedFiles($uploadedFiles);
    }

    /**
     * Finds the uploaded file or set of uploaded files inside [[$uploadedFiles]] according to given name.
     * Name can be either a string HTML form input name, e.g. 'Item[file]' or array path, e.g. `['Item', 'file']`.
     * @param string|array $name HTML form input name or array path.
     * @return UploadedFileInterface|array|null
     * @since 3.0.0
     */
    private function findUploadedFiles($name)
    {
        if (!is_array($name)) {
            $name = preg_split('/\\]\\[|\\[|\\]/s', $name, -1, PREG_SPLIT_NO_EMPTY);
        }
        return ArrayHelper::getValue($this->getUploadedFiles(), $name);
    }

    /**
     * Reduces complex uploaded files structure to the single-level array (list).
     * @param array $uploadedFiles raw set of the uploaded files.
     * @return UploadedFileInterface[] list of uploaded files.
     * @since 3.0.0
     */
    private function reduceUploadedFiles($uploadedFiles)
    {
        return array_reduce($uploadedFiles, function ($carry, $item) {
            if ($item instanceof UploadedFileInterface) {
                $carry[] = $item;
            } else {
                $carry = array_merge($carry, $this->reduceUploadedFiles($item));
            }
            return $carry;
        }, []);
    }

    private $_csrfToken;

    /**
     * Returns the token used to perform CSRF validation.
     *
     * This token is generated in a way to prevent [BREACH attacks](http://breachattack.com/). It may be passed
     * along via a hidden field of an HTML form or an HTTP header value to support CSRF validation.
     * @param bool $regenerate whether to regenerate CSRF token. When this parameter is true, each time
     * this method is called, a new CSRF token will be generated and persisted (in session or cookie).
     * @return string the token used to perform CSRF validation.
     */
    public function getCsrfToken($regenerate = false)
    {
        if ($this->_csrfToken === null || $regenerate) {
            $token = $this->loadCsrfToken();
            if ($regenerate || empty($token)) {
                $token = $this->generateCsrfToken();
            }
            $this->_csrfToken = Yii::$app->security->maskToken($token);
        }

        return $this->_csrfToken;
    }

    /**
     * Loads the CSRF token from cookie or session.
     * @return string the CSRF token loaded from cookie or session. Null is returned if the cookie or session
     * does not have CSRF token.
     */
    protected function loadCsrfToken()
    {
        if ($this->enableCsrfCookie) {
            return $this->getCookies()->getValue($this->csrfParam);
        }

        return Yii::$app->getSession()->get($this->csrfParam);
    }

    /**
     * Generates an unmasked random token used to perform CSRF validation.
     * @return string the random token for CSRF validation.
     */
    protected function generateCsrfToken()
    {
        $token = Yii::$app->getSecurity()->generateRandomString();
        if ($this->enableCsrfCookie) {
            $cookie = $this->createCsrfCookie($token);
            Yii::$app->getResponse()->getCookies()->add($cookie);
        } else {
            Yii::$app->getSession()->set($this->csrfParam, $token);
        }

        return $token;
    }

    /**
     * @return string the CSRF token sent via [[CSRF_HEADER]] by browser. Null is returned if no such header is sent.
     */
    public function getCsrfTokenFromHeader()
    {
        return $this->getHeaderLine(static::CSRF_HEADER);
    }

    /**
     * Creates a cookie with a randomly generated CSRF token.
     * Initial values specified in [[csrfCookie]] will be applied to the generated cookie.
     * @param string $token the CSRF token
     * @return Cookie the generated cookie
     * @see enableCsrfValidation
     */
    protected function createCsrfCookie($token)
    {
        $options = $this->csrfCookie;
        return Yii::createObject(array_merge($options, [
            '__class' => \yii\http\Cookie::class,
            'name' => $this->csrfParam,
            'value' => $token,
        ]));
    }

    /**
     * Performs the CSRF validation.
     *
     * This method will validate the user-provided CSRF token by comparing it with the one stored in cookie or session.
     * This method is mainly called in [[Controller::beforeAction()]].
     *
     * Note that the method will NOT perform CSRF validation if [[enableCsrfValidation]] is false or the HTTP method
     * is among GET, HEAD or OPTIONS.
     *
     * @param string $clientSuppliedToken the user-provided CSRF token to be validated. If null, the token will be retrieved from
     * the [[csrfParam]] POST field or HTTP header.
     * This parameter is available since version 2.0.4.
     * @return bool whether CSRF token is valid. If [[enableCsrfValidation]] is false, this method will return true.
     */
    public function validateCsrfToken($clientSuppliedToken = null)
    {
        $method = $this->getMethod();
        // only validate CSRF token on non-"safe" methods https://tools.ietf.org/html/rfc2616#section-9.1.1
        if (!$this->enableCsrfValidation || in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }

        $trueToken = $this->getCsrfToken();

        if ($clientSuppliedToken !== null) {
            return $this->validateCsrfTokenInternal($clientSuppliedToken, $trueToken);
        }

        return $this->validateCsrfTokenInternal($this->getParsedBodyParam($this->csrfParam), $trueToken)
            || $this->validateCsrfTokenInternal($this->getCsrfTokenFromHeader(), $trueToken);
    }

    /**
     * Validates CSRF token.
     *
     * @param string $clientSuppliedToken The masked client-supplied token.
     * @param string $trueToken The masked true token.
     * @return bool
     */
    private function validateCsrfTokenInternal($clientSuppliedToken, $trueToken)
    {
        if (!is_string($clientSuppliedToken)) {
            return false;
        }

        $security = Yii::$app->security;

        return $security->unmaskToken($clientSuppliedToken) === $security->unmaskToken($trueToken);
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function getAttributes()
    {
        if ($this->_attributes === null) {
            $this->_attributes = $this->defaultAttributes();
        }
        return $this->_attributes;
    }

    /**
     * @param array $attributes attributes derived from the request.
     */
    public function setAttributes(array $attributes)
    {
        $this->_attributes = $attributes;
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function getAttribute($name, $default = null)
    {
        $attributes = $this->getAttributes();
        if (!array_key_exists($name, $attributes)) {
            return $default;
        }

        return $attributes[$name];
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function withAttribute($name, $value)
    {
        $attributes = $this->getAttributes();
        if (array_key_exists($name, $attributes) && $attributes[$name] === $value) {
            return $this;
        }

        $attributes[$name] = $value;

        $newInstance = clone $this;
        $newInstance->setAttributes($attributes);
        return $newInstance;
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function withoutAttribute($name)
    {
        $attributes = $this->getAttributes();
        if (!array_key_exists($name, $attributes)) {
            return $this;
        }

        unset($attributes[$name]);

        $newInstance = clone $this;
        $newInstance->setAttributes($attributes);
        return $newInstance;
    }

    /**
     * Returns default server request attributes to be used in case they are not explicitly set.
     * @return array attributes derived from the request.
     * @since 3.0.0
     */
    protected function defaultAttributes()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        parent::__clone();

        $this->cloneHttpMessageInternals();

        if (is_object($this->_cookies)) {
            $this->_cookies = clone $this->_cookies;
        }

        $this->_parsedBody = false;
    }
}
