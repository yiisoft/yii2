<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\oauth;

use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidParamException;
use Yii;
use yii\helpers\Json;

/**
 * BaseClient is a base class for the OAuth clients.
 *
 * @see http://oauth.net/
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
abstract class BaseClient extends Component
{
	const CONTENT_TYPE_JSON = 'json'; // JSON format
	const CONTENT_TYPE_URLENCODED = 'urlencoded'; // urlencoded query string, like name1=value1&name2=value2
	const CONTENT_TYPE_XML = 'xml'; // XML format
	const CONTENT_TYPE_AUTO = 'auto'; // attempts to determine format automatically

	/**
	 * @var string protocol version.
	 */
	public $version = '1.0';
	/**
	 * @var string URL, which user will be redirected after authentication at the OAuth provider web site.
	 * Note: this should be absolute URL (with http:// or https:// leading).
	 * By default current URL will be used.
	 */
	private $_returnUrl = '';
	/**
	 * @var string API base URL.
	 */
	public $apiBaseUrl = '';
	/**
	 * @var string authorize URL.
	 */
	public $authUrl = '';
	/**
	 * @var string auth request scope.
	 */
	public $scope = '';
	/**
	 * @var array cURL request options. Option values from this field will overwrite corresponding
	 * values from {@link defaultCurlOptions()}.
	 */
	private $_curlOptions = [];
	/**
	 * @var Token|array access token instance or its array configuration.
	 */
	private $_accessToken = null;
	/**
	 * @var signature\BaseMethod|array signature method instance or its array configuration.
	 */
	private $_signatureMethod = [];

	/**
	 * @param string $returnUrl return URL
	 */
	public function setReturnUrl($returnUrl)
	{
		$this->_returnUrl = $returnUrl;
	}

	/**
	 * @return string return URL.
	 */
	public function getReturnUrl()
	{
		if (empty($this->_returnUrl)) {
			$this->_returnUrl = $this->defaultReturnUrl();
		}
		return $this->_returnUrl;
	}

	/**
	 * @param array $curlOptions cURL options.
	 */
	public function setCurlOptions(array $curlOptions)
	{
		$this->_curlOptions = $curlOptions;
	}

	/**
	 * @return array cURL options.
	 */
	public function getCurlOptions()
	{
		return $this->_curlOptions;
	}

	/**
	 * @param array|Token $token
	 */
	public function setAccessToken($token)
	{
		if (!is_object($token)) {
			$token = $this->createToken($token);
		}
		$this->_accessToken = $token;
		$this->saveAccessToken($token);
	}

	/**
	 * @return Token auth token instance.
	 */
	public function getAccessToken()
	{
		if (!is_object($this->_accessToken)) {
			$this->_accessToken = $this->restoreAccessToken();
		}
		return $this->_accessToken;
	}

	/**
	 * @param array|signature\BaseMethod $signatureMethod signature method instance or its array configuration.
	 * @throws InvalidParamException on wrong argument.
	 */
	public function setSignatureMethod($signatureMethod)
	{
		if (!is_object($signatureMethod) && !is_array($signatureMethod)) {
			throw new InvalidParamException('"'.get_class($this).'::signatureMethod" should be instance of "\yii\autclient\oauth\signature\BaseMethod" or its array configuration. "' . gettype($signatureMethod) . '" has been given.');
		}
		$this->_signatureMethod = $signatureMethod;
	}

	/**
	 * @return signature\BaseMethod signature method instance.
	 */
	public function getSignatureMethod()
	{
		if (!is_object($this->_signatureMethod)) {
			$this->_signatureMethod = $this->createSignatureMethod($this->_signatureMethod);
		}
		return $this->_signatureMethod;
	}

	/**
	 * Composes default {@link returnUrl} value.
	 * @return string return URL.
	 */
	protected function defaultReturnUrl()
	{
		return Yii::$app->getRequest()->getAbsoluteUrl();
	}

	/**
	 * Sends HTTP request.
	 * @param string $method request type.
	 * @param string $url request URL.
	 * @param array $params request params.
	 * @return array response.
	 * @throws Exception on failure.
	 */
	protected function sendRequest($method, $url, array $params = [])
	{
		$curlOptions = $this->mergeCurlOptions(
			$this->defaultCurlOptions(),
			$this->getCurlOptions(),
			array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_URL => $url,
			),
			$this->composeRequestCurlOptions(strtoupper($method), $url, $params)
		);
		$curlResource = curl_init();
		foreach ($curlOptions as $option => $value) {
			curl_setopt($curlResource, $option, $value);
		}
		$response = curl_exec($curlResource);
		$responseHeaders = curl_getinfo($curlResource);

		// check cURL error
		$errorNumber = curl_errno($curlResource);
		$errorMessage = curl_error($curlResource);

		curl_close($curlResource);

		if ($errorNumber > 0) {
			throw new Exception('Curl error requesting "' .  $url . '": #' . $errorNumber . ' - ' . $errorMessage);
		}
		if ($responseHeaders['http_code'] != 200) {
			throw new Exception('Request failed with code: ' . $responseHeaders['http_code'] . ', message: ' . $response);
		}
		return $this->processResponse($response, $this->determineContentTypeByHeaders($responseHeaders));
	}

	/**
	 * Merge CUrl options.
	 * If each options array has an element with the same key value, the latter
	 * will overwrite the former.
	 * @param array $options1 options to be merged to.
	 * @param array $options2 options to be merged from. You can specify additional
	 * arrays via third argument, fourth argument etc.
	 * @return array merged options (the original options are not changed.)
	 */
	protected function mergeCurlOptions($options1, $options2)
	{
		$args = func_get_args();
		$res = array_shift($args);
		while (!empty($args)) {
			$next = array_shift($args);
			foreach ($next as $k => $v) {
				$res[$k]=$v;
			}
		}
		return $res;
	}

	/**
	 * Returns default cURL options.
	 * @return array cURL options.
	 */
	protected function defaultCurlOptions()
	{
		return [
			CURLOPT_USERAGENT => Yii::$app->name . ' OAuth Client',
			CURLOPT_CONNECTTIMEOUT => 30,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYPEER => false,
		];
	}

	/**
	 * Processes raw response converting it to actual data.
	 * @param string $rawResponse raw response.
	 * @param string $contentType response content type.
	 * @throws Exception on failure.
	 * @return array actual response.
	 */
	protected function processResponse($rawResponse, $contentType = self::CONTENT_TYPE_AUTO)
	{
		if (empty($rawResponse)) {
			return [];
		}
		switch ($contentType) {
			case self::CONTENT_TYPE_AUTO: {
				$contentType = $this->determineContentTypeByRaw($rawResponse);
				if ($contentType == self::CONTENT_TYPE_AUTO) {
					throw new Exception('Unable to determine response content type automatically.');
				}
				$response = $this->processResponse($rawResponse, $contentType);
				break;
			}
			case self::CONTENT_TYPE_JSON: {
				$response = Json::decode($rawResponse, true);
				if (isset($response['error'])) {
					throw new Exception('Response error: ' . $response['error']);
				}
				break;
			}
			case self::CONTENT_TYPE_URLENCODED: {
				$response = [];
				parse_url($rawResponse, $response);
				break;
			}
			case self::CONTENT_TYPE_XML: {
				$response = $this->convertXmlToArray($rawResponse);
				break;
			}
			default: {
				throw new Exception('Unknown response type "' . $contentType . '".');
			}
		}
		return $response;
	}

	/**
	 * Converts XML document to array.
	 * @param string|\SimpleXMLElement $xml xml to process.
	 * @return array XML array representation.
	 */
	protected function convertXmlToArray($xml)
	{
		if (!is_object($xml)) {
			$xml = simplexml_load_string($xml);
		}
		$result = (array)$xml;
		foreach ($result as $key => $value) {
			if (is_object($value)) {
				$result[$key] = $this->convertXmlToArray($value);
			}
		}
		return $result;
	}

	/**
	 * Attempts to determine HTTP request content type by headers.
	 * @param array $headers request headers.
	 * @return string content type.
	 */
	protected function determineContentTypeByHeaders(array $headers)
	{
		if (isset($headers['content_type'])) {
			if (stripos($headers['content_type'], 'json') !== false) {
				return self::CONTENT_TYPE_JSON;
			}
			if (stripos($headers['content_type'], 'urlencoded') !== false) {
				return self::CONTENT_TYPE_URLENCODED;
			}
			if (stripos($headers['content_type'], 'xml') !== false) {
				return self::CONTENT_TYPE_XML;
			}
		}
		return self::CONTENT_TYPE_AUTO;
	}

	/**
	 * Attempts to determine the content type from raw content.
	 * @param string $rawContent raw response content.
	 * @return string response type.
	 */
	protected function determineContentTypeByRaw($rawContent)
	{
		if (preg_match('/^\\{.*\\}$/is', $rawContent)) {
			return self::CONTENT_TYPE_JSON;
		}
		if (preg_match('/^[^=|^&]+=[^=|^&]+(&[^=|^&]+=[^=|^&]+)*$/is', $rawContent)) {
			return self::CONTENT_TYPE_URLENCODED;
		}
		if (preg_match('/^<.*>$/is', $rawContent)) {
			return self::CONTENT_TYPE_XML;
		}
		return self::CONTENT_TYPE_AUTO;
	}

	/**
	 * Creates signature method instance from its configuration.
	 * @param array $signatureMethodConfig signature method configuration.
	 * @return signature\BaseMethod signature method instance.
	 */
	protected function createSignatureMethod(array $signatureMethodConfig)
	{
		if (!array_key_exists('class', $signatureMethodConfig)) {
			$signatureMethodConfig['class'] = signature\HmacSha1::className();
		}
		return Yii::createObject($signatureMethodConfig);
	}

	/**
	 * Creates token from its configuration.
	 * @param array $tokenConfig token configuration.
	 * @return Token token instance.
	 */
	protected function createToken(array $tokenConfig = [])
	{
		if (!array_key_exists('class', $tokenConfig)) {
			$tokenConfig['class'] = Token::className();
		}
		return Yii::createObject($tokenConfig);
	}

	/**
	 * Composes URL from base URL and GET params.
	 * @param string $url base URL.
	 * @param array $params GET params.
	 * @return string composed URL.
	 */
	protected function composeUrl($url, array $params = [])
	{
		if (strpos($url, '?') === false) {
			$url .= '?';
		} else {
			$url .= '&';
		}
		$url .= http_build_query($params, '', '&', PHP_QUERY_RFC3986);
		return $url;
	}

	/**
	 * Saves token as persistent state.
	 * @param Token $token auth token
	 * @return static self reference.
	 */
	protected function saveAccessToken(Token $token)
	{
		return $this->setState('token', $token);
	}

	/**
	 * Restores access token.
	 * @return Token auth token.
	 */
	protected function restoreAccessToken()
	{
		$token = $this->getState('token');
		if (is_object($token)) {
			/* @var $token Token */
			if ($token->getIsExpired()) {
				$token = $this->refreshAccessToken($token);
			}
		}
		return $token;
	}

	/**
	 * Sets persistent state.
	 * @param string $key state key.
	 * @param mixed $value state value
	 * @return static self reference.
	 */
	protected function setState($key, $value)
	{
		$session = Yii::$app->getSession();
		$key = $this->getStateKeyPrefix() . $key;
		$session->set($key, $value);
		return $this;
	}

	/**
	 * Returns persistent state value.
	 * @param string $key state key.
	 * @return mixed state value.
	 */
	protected function getState($key)
	{
		$session = Yii::$app->getSession();
		$key = $this->getStateKeyPrefix() . $key;
		$value = $session->get($key);
		return $value;
	}

	/**
	 * Removes persistent state value.
	 * @param string $key state key.
	 * @return boolean success.
	 */
	protected function removeState($key)
	{
		$session = Yii::$app->getSession();
		$key = $this->getStateKeyPrefix() . $key;
		$session->remove($key);
		return true;
	}

	/**
	 * Returns session key prefix, which is used to store internal states.
	 * @return string session key prefix.
	 */
	protected function getStateKeyPrefix()
	{
		return get_class($this) . '_' . sha1($this->authUrl) . '_';
	}

	/**
	 * Performs request to the OAuth API.
	 * @param string $apiSubUrl API sub URL, which will be append to [[apiBaseUrl]], or absolute API URL.
	 * @param string $method request method.
	 * @param array $params request parameters.
	 * @return array API response
	 * @throws Exception on failure.
	 */
	public function api($apiSubUrl, $method = 'GET', array $params = [])
	{
		if (preg_match('/^https?:\\/\\//is', $apiSubUrl)) {
			$url = $apiSubUrl;
		} else {
			$url = $this->apiBaseUrl . '/' . $apiSubUrl;
		}
		$accessToken = $this->getAccessToken();
		if (!is_object($accessToken) || !$accessToken->getIsValid()) {
			throw new Exception('Invalid access token.');
		}
		return $this->apiInternal($accessToken, $url, $method, $params);
	}

	/**
	 * Composes HTTP request CUrl options, which will be merged with the default ones.
	 * @param string $method request type.
	 * @param string $url request URL.
	 * @param array $params request params.
	 * @return array CUrl options.
	 * @throws Exception on failure.
	 */
	abstract protected function composeRequestCurlOptions($method, $url, array $params);

	/**
	 * Gets new auth token to replace expired one.
	 * @param Token $token expired auth token.
	 * @return Token new auth token.
	 */
	abstract public function refreshAccessToken(Token $token);

	/**
	 * Performs request to the OAuth API.
	 * @param Token $accessToken actual access token.
	 * @param string $url absolute API URL.
	 * @param string $method request method.
	 * @param array $params request parameters.
	 * @return array API response.
	 * @throws Exception on failure.
	 */
	abstract protected function apiInternal($accessToken, $url, $method, array $params);
}