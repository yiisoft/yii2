<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient;

use Yii;
use yii\base\Exception;

/**
 * OAuth2 serves as a client for the OAuth 2 flow.
 *
 * In oder to acquire access token perform following sequence:
 *
 * ~~~
 * use yii\authclient\OAuth2;
 *
 * $oauthClient = new OAuth2();
 * $url = $oauthClient->buildAuthUrl(); // Build authorization URL
 * Yii::$app->getResponse()->redirect($url); // Redirect to authorization URL.
 * // After user returns at our site:
 * $code = $_GET['code'];
 * $accessToken = $oauthClient->fetchAccessToken($code); // Get access token
 * ~~~
 *
 * @see http://oauth.net/2/
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class OAuth2 extends BaseOAuth
{
	/**
	 * @var string protocol version.
	 */
	public $version = '2.0';
	/**
	 * @var string OAuth client ID.
	 */
	public $clientId;
	/**
	 * @var string OAuth client secret.
	 */
	public $clientSecret;
	/**
	 * @var string token request URL endpoint.
	 */
	public $tokenUrl;

	/**
	 * Composes user authorization URL.
	 * @param array $params additional auth GET params.
	 * @return string authorization URL.
	 */
	public function buildAuthUrl(array $params = [])
	{
		$defaultParams = [
			'client_id' => $this->clientId,
			'response_type' => 'code',
			'redirect_uri' => $this->getReturnUrl(),
			'xoauth_displayname' => Yii::$app->name,
		];
		if (!empty($this->scope)) {
			$defaultParams['scope'] = $this->scope;
		}
		return $this->composeUrl($this->authUrl, array_merge($defaultParams, $params));
	}

	/**
	 * Fetches access token from authorization code.
	 * @param string $authCode authorization code, usually comes at $_GET['code'].
	 * @param array $params additional request params.
	 * @return OAuthToken access token.
	 */
	public function fetchAccessToken($authCode, array $params = [])
	{
		$defaultParams = [
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'code' => $authCode,
			'grant_type' => 'authorization_code',
			'redirect_uri' => $this->getReturnUrl(),
		];
		$response = $this->sendRequest('POST', $this->tokenUrl, array_merge($defaultParams, $params));
		$token = $this->createToken(['params' => $response]);
		$this->setAccessToken($token);
		return $token;
	}

	/**
	 * Composes HTTP request CUrl options, which will be merged with the default ones.
	 * @param string $method request type.
	 * @param string $url request URL.
	 * @param array $params request params.
	 * @return array CUrl options.
	 * @throws Exception on failure.
	 */
	protected function composeRequestCurlOptions($method, $url, array $params)
	{
		$curlOptions = [];
		switch ($method) {
			case 'GET': {
				$curlOptions[CURLOPT_URL] = $this->composeUrl($url, $params);
				break;
			}
			case 'POST': {
				$curlOptions[CURLOPT_POST] = true;
				$curlOptions[CURLOPT_HTTPHEADER] = ['Content-type: application/x-www-form-urlencoded'];
				$curlOptions[CURLOPT_POSTFIELDS] = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
				break;
			}
			case 'HEAD':
			case 'PUT':
			case 'DELETE': {
				$curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
				if (!empty($params)) {
					$curlOptions[CURLOPT_URL] = $this->composeUrl($url, $params);
				}
				break;
			}
			default: {
				throw new Exception("Unknown request method '{$method}'.");
			}
		}
		return $curlOptions;
	}

	/**
	 * Performs request to the OAuth API.
	 * @param OAuthToken $accessToken actual access token.
	 * @param string $url absolute API URL.
	 * @param string $method request method.
	 * @param array $params request parameters.
	 * @return array API response.
	 * @throws Exception on failure.
	 */
	protected function apiInternal($accessToken, $url, $method, array $params)
	{
		$params['access_token'] = $accessToken->getToken();
		return $this->sendRequest($method, $url, $params);
	}

	/**
	 * Gets new auth token to replace expired one.
	 * @param OAuthToken $token expired auth token.
	 * @return OAuthToken new auth token.
	 */
	public function refreshAccessToken(OAuthToken $token)
	{
		$params = [
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'grant_type' => 'refresh_token'
		];
		$params = array_merge($token->getParams(), $params);
		$response = $this->sendRequest('POST', $this->tokenUrl, $params);
		return $response;
	}

	/**
	 * Composes default {@link returnUrl} value.
	 * @return string return URL.
	 */
	protected function defaultReturnUrl()
	{
		$params = $_GET;
		unset($params['code']);
		$params[0] = Yii::$app->controller->getRoute();
		return Yii::$app->getUrlManager()->createAbsoluteUrl($params);
	}

	/**
	 * Creates token from its configuration.
	 * @param array $tokenConfig token configuration.
	 * @return OAuthToken token instance.
	 */
	protected function createToken(array $tokenConfig = [])
	{
		$tokenConfig['tokenParamKey'] = 'access_token';
		return parent::createToken($tokenConfig);
	}
}
