<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\HttpException;
use yii\base\InvalidConfigException;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Request extends \yii\base\Request
{
	/**
	 * @var boolean whether to enable CSRF (Cross-Site Request Forgery) validation. Defaults to false.
	 * By setting this property to true, forms submitted to an Yii Web application must be originated
	 * from the same application. If not, a 400 HTTP exception will be raised.
	 *
	 * Note, this feature requires that the user client accepts cookie. Also, to use this feature,
	 * forms submitted via POST method must contain a hidden input whose name is specified by [[csrfTokenName]].
	 * You may use [[\yii\web\Html::beginForm()]] to generate his hidden input.
	 * @see http://en.wikipedia.org/wiki/Cross-site_request_forgery
	 */
	public $enableCsrfValidation = false;
	/**
	 * @var string the name of the token used to prevent CSRF. Defaults to 'YII_CSRF_TOKEN'.
	 * This property is effectively only when {@link enableCsrfValidation} is true.
	 */
	public $csrfTokenName = '_csrf';
	/**
	 * @var array the configuration of the CSRF cookie. This property is used only when [[enableCsrfValidation]] is true.
	 * @see Cookie
	 */
	public $csrfCookie = array('httponly' => true);
	/**
	 * @var boolean whether cookies should be validated to ensure they are not tampered. Defaults to true.
	 */
	public $enableCookieValidation = true;
	/**
	 * @var string the secret key used for cookie validation. If not set, a random key will be generated and used.
	 */
	public $cookieValidationKey;
	/**
	 * @var string|boolean the name of the POST parameter that is used to indicate if a request is a PUT or DELETE
	 * request tunneled through POST. Default to '_method'.
	 * @see getRequestMethod
	 * @see getRestParams
	 */
	public $restVar = '_method';

	private $_cookies;


	/**
	 * Resolves the current request into a route and the associated parameters.
	 * @return array the first element is the route, and the second is the associated parameters.
	 * @throws HttpException if the request cannot be resolved.
	 */
	public function resolve()
	{
		$this->validateCsrfToken();

		$result = Yii::$app->getUrlManager()->parseRequest($this);
		if ($result !== false) {
			list ($route, $params) = $result;
			$_GET = array_merge($_GET, $params);
			return array($route, $_GET);
		} else {
			throw new HttpException(404, Yii::t('yii|Page not found.'));
		}
	}

	/**
	 * Returns the method of the current request (e.g. GET, POST, HEAD, PUT, DELETE).
	 * @return string request method, such as GET, POST, HEAD, PUT, DELETE.
	 * The value returned is turned into upper case.
	 */
	public function getRequestMethod()
	{
		if (isset($_POST[$this->restVar])) {
			return strtoupper($_POST[$this->restVar]);
		} else {
			return isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
		}
	}

	/**
	 * Returns whether this is a POST request.
	 * @return boolean whether this is a POST request.
	 */
	public function getIsPostRequest()
	{
		return $this->getRequestMethod() === 'POST';
	}

	/**
	 * Returns whether this is a DELETE request.
	 * @return boolean whether this is a DELETE request.
	 */
	public function getIsDeleteRequest()
	{
		return $this->getRequestMethod() === 'DELETE';
	}

	/**
	 * Returns whether this is a PUT request.
	 * @return boolean whether this is a PUT request.
	 */
	public function getIsPutRequest()
	{
		return $this->getRequestMethod() === 'PUT';
	}

	/**
	 * Returns whether this is an AJAX (XMLHttpRequest) request.
	 * @return boolean whether this is an AJAX (XMLHttpRequest) request.
	 */
	public function getIsAjaxRequest()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}

	/**
	 * Returns whether this is an Adobe Flash or Flex request.
	 * @return boolean whether this is an Adobe Flash or Adobe Flex request.
	 */
	public function getIsFlashRequest()
	{
		return isset($_SERVER['HTTP_USER_AGENT']) &&
			(stripos($_SERVER['HTTP_USER_AGENT'], 'Shockwave') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'Flash') !== false);
	}

	private $_restParams;

	/**
	 * Returns the request parameters for the RESTful request.
	 * @return array the RESTful request parameters
	 * @see getRequestMethod
	 */
	public function getRestParams()
	{
		if ($this->_restParams === null) {
			if (isset($_POST[$this->restVar])) {
				$this->_restParams = $_POST;
			} else {
				$this->_restParams = array();
				if (function_exists('mb_parse_str')) {
					mb_parse_str($this->getRawBody(), $this->_restParams);
				} else {
					parse_str($this->getRawBody(), $this->_restParams);
				}
			}
		}
		return $this->_restParams;
	}

	private $_rawBody;

	/**
	 * Returns the raw HTTP request body.
	 * @return string the request body
	 */
	public function getRawBody()
	{
		if ($this->_rawBody === null) {
			$this->_rawBody = file_get_contents('php://input');
		}
		return $this->_rawBody;
	}

	/**
	 * Sets the RESTful parameters.
	 * @param array $values the RESTful parameters (name-value pairs)
	 */
	public function setRestParams($values)
	{
		$this->_restParams = $values;
	}

	/**
	 * Returns the named RESTful parameter value.
	 * @param string $name the parameter name
	 * @param mixed $defaultValue the default parameter value if the parameter does not exist.
	 * @return mixed the parameter value
	 */
	public function getRestParam($name, $defaultValue = null)
	{
		$params = $this->getRestParams();
		return isset($params[$name]) ? $params[$name] : $defaultValue;
	}

	/**
	 * Returns the named GET parameter value.
	 * If the GET parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the GET parameter name
	 * @param mixed $defaultValue the default parameter value if the GET parameter does not exist.
	 * @return mixed the GET parameter value
	 * @see getPost
	 */
	public function getParam($name, $defaultValue = null)
	{
		return isset($_GET[$name]) ? $_GET[$name] : $defaultValue;
	}

	/**
	 * Returns the named POST parameter value.
	 * If the POST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the POST parameter name
	 * @param mixed $defaultValue the default parameter value if the POST parameter does not exist.
	 * @return mixed the POST parameter value
	 * @see getParam
	 */
	public function getPost($name, $defaultValue = null)
	{
		return isset($_POST[$name]) ? $_POST[$name] : $defaultValue;
	}

	/**
	 * Returns the named DELETE parameter value.
	 * @param string $name the DELETE parameter name
	 * @param mixed $defaultValue the default parameter value if the DELETE parameter does not exist.
	 * @return mixed the DELETE parameter value
	 */
	public function getDelete($name, $defaultValue = null)
	{
		return $this->getIsDeleteRequest() ? $this->getRestParam($name, $defaultValue) : null;
	}

	/**
	 * Returns the named PUT parameter value.
	 * @param string $name the PUT parameter name
	 * @param mixed $defaultValue the default parameter value if the PUT parameter does not exist.
	 * @return mixed the PUT parameter value
	 */
	public function getPut($name, $defaultValue = null)
	{
		return $this->getIsPutRequest() ? $this->getRestParam($name, $defaultValue) : null;
	}

	private $_hostInfo;

	/**
	 * Returns the schema and host part of the current request URL.
	 * The returned URL does not have an ending slash.
	 * By default this is determined based on the user request information.
	 * You may explicitly specify it by setting the [[setHostInfo()|hostInfo]] property.
	 * @return string schema and hostname part (with port number if needed) of the request URL (e.g. `http://www.yiiframework.com`)
	 * @see setHostInfo
	 */
	public function getHostInfo()
	{
		if ($this->_hostInfo === null) {
			$secure = $this->getIsSecureConnection();
			$http = $secure ? 'https' : 'http';
			if (isset($_SERVER['HTTP_HOST'])) {
				$this->_hostInfo = $http . '://' . $_SERVER['HTTP_HOST'];
			} else {
				$this->_hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];
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
	 * @param string $value the schema and host part of the application URL. The trailing slashes will be removed.
	 */
	public function setHostInfo($value)
	{
		$this->_hostInfo = rtrim($value, '/');
	}

	private $_baseUrl;

	/**
	 * Returns the relative URL for the application.
	 * This is similar to [[scriptUrl]] except that it does not include the script file name,
	 * and the ending slashes are removed.
	 * @return string the relative URL for the application
	 * @see setScriptUrl
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
			if (basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
				$this->_scriptUrl = $_SERVER['SCRIPT_NAME'];
			} elseif (basename($_SERVER['PHP_SELF']) === $scriptName) {
				$this->_scriptUrl = $_SERVER['PHP_SELF'];
			} elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
				$this->_scriptUrl = $_SERVER['ORIG_SCRIPT_NAME'];
			} elseif (($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false) {
				$this->_scriptUrl = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $scriptName;
			} elseif (isset($_SERVER['DOCUMENT_ROOT']) && strpos($scriptFile, $_SERVER['DOCUMENT_ROOT']) === 0) {
				$this->_scriptUrl = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $scriptFile));
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
		$this->_scriptUrl = '/' . trim($value, '/');
	}

	private $_scriptFile;

	/**
	 * Returns the entry script file path.
	 * The default implementation will simply return `$_SERVER['SCRIPT_FILENAME']`.
	 * @return string the entry script file path
	 */
	public function getScriptFile()
	{
		return isset($this->_scriptFile) ? $this->_scriptFile : $_SERVER['SCRIPT_FILENAME'];
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
		$this->_pathInfo = trim($value, '/');
	}

	/**
	 * Resolves the path info part of the currently requested URL.
	 * A path info refers to the part that is after the entry script and before the question mark (query string).
	 * The starting and ending slashes are both removed.
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
				)*$%xs', $pathInfo)) {
			$pathInfo = utf8_encode($pathInfo);
		}

		$scriptUrl = $this->getScriptUrl();
		$baseUrl = $this->getBaseUrl();
		if (strpos($pathInfo, $scriptUrl) === 0) {
			$pathInfo = substr($pathInfo, strlen($scriptUrl));
		} elseif ($baseUrl === '' || strpos($pathInfo, $baseUrl) === 0) {
			$pathInfo = substr($pathInfo, strlen($baseUrl));
		} elseif (strpos($_SERVER['PHP_SELF'], $scriptUrl) === 0) {
			$pathInfo = substr($_SERVER['PHP_SELF'], strlen($scriptUrl));
		} else {
			throw new InvalidConfigException('Unable to determine the path info of the current request.');
		}

		return trim($pathInfo, '/');
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
	 * @return string the currently requested relative URL. Note that the URI returned is URL-encoded.
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
	 * @return string|boolean the request URI portion for the currently requested URL.
	 * Note that the URI returned is URL-encoded.
	 * @throws InvalidConfigException if the request URI cannot be determined due to unusual server configuration
	 */
	protected function resolveRequestUri()
	{
		if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // IIS
			$requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
		} elseif (isset($_SERVER['REQUEST_URI'])) {
			$requestUri = $_SERVER['REQUEST_URI'];
			if ($requestUri !== '' && $requestUri[0] !== '/') {
				$requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
			}
		} elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 CGI
			$requestUri = $_SERVER['ORIG_PATH_INFO'];
			if (!empty($_SERVER['QUERY_STRING'])) {
				$requestUri .= '?' . $_SERVER['QUERY_STRING'];
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
		return isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
	}

	/**
	 * Return if the request is sent via secure channel (https).
	 * @return boolean if the request is sent via secure channel (https)
	 */
	public function getIsSecureConnection()
	{
		return !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off');
	}

	/**
	 * Returns the server name.
	 * @return string server name
	 */
	public function getServerName()
	{
		return $_SERVER['SERVER_NAME'];
	}

	/**
	 * Returns the server port number.
	 * @return integer server port number
	 */
	public function getServerPort()
	{
		return (int)$_SERVER['SERVER_PORT'];
	}

	/**
	 * Returns the URL referrer, null if not present
	 * @return string URL referrer, null if not present
	 */
	public function getReferrer()
	{
		return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
	}

	/**
	 * Returns the user agent, null if not present.
	 * @return string user agent, null if not present
	 */
	public function getUserAgent()
	{
		return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
	}

	/**
	 * Returns the user IP address.
	 * @return string user IP address
	 */
	public function getUserIP()
	{
		return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
	}

	/**
	 * Returns the user host name, null if it cannot be determined.
	 * @return string user host name, null if cannot be determined
	 */
	public function getUserHost()
	{
		return isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : null;
	}

	/**
	 * Returns user browser accept types, null if not present.
	 * @return string user browser accept types, null if not present
	 */
	public function getAcceptTypes()
	{
		return isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null;
	}

	private $_port;

	/**
	 * Returns the port to use for insecure requests.
	 * Defaults to 80, or the port specified by the server if the current
	 * request is insecure.
	 * @return integer port number for insecure requests.
	 * @see setPort
	 */
	public function getPort()
	{
		if ($this->_port === null) {
			$this->_port = !$this->getIsSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 80;
		}
		return $this->_port;
	}

	/**
	 * Sets the port to use for insecure requests.
	 * This setter is provided in case a custom port is necessary for certain
	 * server configurations.
	 * @param integer $value port number.
	 */
	public function setPort($value)
	{
		if ($value != $this->_port) {
			$this->_port = (int)$value;
			$this->_hostInfo = null;
		}
	}

	private $_securePort;

	/**
	 * Returns the port to use for secure requests.
	 * Defaults to 443, or the port specified by the server if the current
	 * request is secure.
	 * @return integer port number for secure requests.
	 * @see setSecurePort
	 */
	public function getSecurePort()
	{
		if ($this->_securePort === null) {
			$this->_securePort = $this->getIsSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 443;
		}
		return $this->_securePort;
	}

	/**
	 * Sets the port to use for secure requests.
	 * This setter is provided in case a custom port is necessary for certain
	 * server configurations.
	 * @param integer $value port number.
	 */
	public function setSecurePort($value)
	{
		if ($value != $this->_securePort) {
			$this->_securePort = (int)$value;
			$this->_hostInfo = null;
		}
	}

	private $_preferredLanguages;

	/**
	 * Returns the user preferred languages.
	 * The languages returned are ordered by user's preference, starting with the language that the user
	 * prefers the most.
	 * @return string the user preferred languages. An empty array may be returned if the user has no preference.
	 */
	public function getPreferredLanguages()
	{
		if ($this->_preferredLanguages === null) {
			if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && ($n = preg_match_all('/([\w\-_]+)\s*(;\s*q\s*=\s*(\d*\.\d*))?/', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches)) > 0) {
				$languages = array();
				for ($i = 0; $i < $n; ++$i) {
					$languages[$matches[1][$i]] = empty($matches[3][$i]) ? 1.0 : floatval($matches[3][$i]);
				}
				arsort($languages);
				$this->_preferredLanguages = array_keys($languages);
			} else {
				$this->_preferredLanguages = array();
			}
		}
		return $this->_preferredLanguages;
	}

	/**
	 * Returns the language most preferred by the user.
	 * @return string|boolean the language most preferred by the user. If the user has no preference, false
	 * will be returned.
	 */
	public function getPreferredLanguage()
	{
		$languages = $this->getPreferredLanguages();
		return isset($languages[0]) ? $languages[0] : false;
	}

	/**
	 * Returns the cookie collection.
	 * Through the returned cookie collection, you may access a cookie using the following syntax:
	 *
	 * ~~~
	 * $cookie = $request->cookies['name']
	 * if ($cookie !== null) {
	 *     $value = $cookie->value;
	 * }
	 *
	 * // alternatively
	 * $value = $request->cookies->getValue('name');
	 * ~~~
	 *
	 * @return CookieCollection the cookie collection.
	 */
	public function getCookies()
	{
		if ($this->_cookies === null) {
			$this->_cookies = new CookieCollection(array(
				'enableValidation' => $this->enableCookieValidation,
				'validationKey' => $this->cookieValidationKey,
			));
		}
		return $this->_cookies;
	}

	private $_csrfToken;

	/**
	 * Returns the random token used to perform CSRF validation.
	 * The token will be read from cookie first. If not found, a new token will be generated.
	 * @return string the random token for CSRF validation.
	 * @see enableCsrfValidation
	 */
	public function getCsrfToken()
	{
		if ($this->_csrfToken === null) {
			$cookies = $this->getCookies();
			if (($this->_csrfToken = $cookies->getValue($this->csrfTokenName)) === null) {
				$cookie = $this->createCsrfCookie();
				$this->_csrfToken = $cookie->value;
				$cookies->add($cookie);
			}
		}

		return $this->_csrfToken;
	}

	/**
	 * Creates a cookie with a randomly generated CSRF token.
	 * Initial values specified in [[csrfCookie]] will be applied to the generated cookie.
	 * @return Cookie the generated cookie
	 * @see enableCsrfValidation
	 */
	protected function createCsrfCookie()
	{
		$options = $this->csrfCookie;
		$options['name'] = $this->csrfTokenName;
		$options['value'] = sha1(uniqid(mt_rand(), true));
		return new Cookie($options);
	}

	/**
	 * Performs the CSRF validation.
	 * The method will compare the CSRF token obtained from a cookie and from a POST field.
	 * If they are different, a CSRF attack is detected and a 400 HTTP exception will be raised.
	 * @throws HttpException if the validation fails
	 */
	public function validateCsrfToken()
	{
		if (!$this->enableCsrfValidation) {
			return;
		}
		$method = $this->getRequestMethod();
		if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
			$cookies = $this->getCookies();
			switch ($method) {
				case 'POST':
					$token = $this->getPost($this->csrfTokenName);
					break;
				case 'PUT':
					$token = $this->getPut($this->csrfTokenName);
					break;
				case 'DELETE':
					$token = $this->getDelete($this->csrfTokenName);
			}

			if (empty($token) || $cookies->getValue($this->csrfTokenName) !== $token) {
				throw new HttpException(400, Yii::t('yii|Unable to verify your data submission.'));
			}
		}
	}
}

