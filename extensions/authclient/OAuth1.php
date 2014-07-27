<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient;

use yii\base\Exception;
use Yii;

/**
 * OAuth1 serves as a client for the OAuth 1/1.0a flow.
 *
 * In oder to acquire access token perform following sequence:
 *
 * ~~~
 * use yii\authclient\OAuth1;
 *
 * $oauthClient = new OAuth1();
 * $requestToken = $oauthClient->fetchRequestToken(); // Get request token
 * $url = $oauthClient->buildAuthUrl($requestToken); // Get authorization URL
 * return Yii::$app->getResponse()->redirect($url); // Redirect to authorization URL
 * // After user returns at our site:
 * $accessToken = $oauthClient->fetchAccessToken($requestToken); // Upgrade to access token
 * ~~~
 *
 * @see http://oauth.net/
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class OAuth1 extends BaseOAuth
{
    /**
     * @var string protocol version.
     */
    public $version = '1.0';
    /**
     * @var string OAuth consumer key.
     */
    public $consumerKey;
    /**
     * @var string OAuth consumer secret.
     */
    public $consumerSecret;
    /**
     * @var string OAuth request token URL.
     */
    public $requestTokenUrl;
    /**
     * @var string request token HTTP method.
     */
    public $requestTokenMethod = 'GET';
    /**
     * @var string OAuth access token URL.
     */
    public $accessTokenUrl;
    /**
     * @var string access token HTTP method.
     */
    public $accessTokenMethod = 'GET';

    /**
     * Fetches the OAuth request token.
     * @param array $params additional request params.
     * @return OAuthToken request token.
     */
    public function fetchRequestToken(array $params = [])
    {
        $this->removeState('token');
        $defaultParams = [
            'oauth_consumer_key' => $this->consumerKey,
            'oauth_callback' => $this->getReturnUrl(),
            //'xoauth_displayname' => Yii::$app->name,
        ];
        if (!empty($this->scope)) {
            $defaultParams['scope'] = $this->scope;
        }
        $response = $this->sendSignedRequest($this->requestTokenMethod, $this->requestTokenUrl, array_merge($defaultParams, $params));
        $token = $this->createToken([
            'params' => $response
        ]);
        $this->setState('requestToken', $token);

        return $token;
    }

    /**
     * Composes user authorization URL.
     * @param OAuthToken $requestToken OAuth request token.
     * @param array $params additional request params.
     * @return string authorize URL
     * @throws Exception on failure.
     */
    public function buildAuthUrl(OAuthToken $requestToken = null, array $params = [])
    {
        if (!is_object($requestToken)) {
            $requestToken = $this->getState('requestToken');
            if (!is_object($requestToken)) {
                throw new Exception('Request token is required to build authorize URL!');
            }
        }
        $params['oauth_token'] = $requestToken->getToken();

        return $this->composeUrl($this->authUrl, $params);
    }

    /**
     * Fetches OAuth access token.
     * @param OAuthToken $requestToken OAuth request token.
     * @param string $oauthVerifier OAuth verifier.
     * @param array $params additional request params.
     * @return OAuthToken OAuth access token.
     * @throws Exception on failure.
     */
    public function fetchAccessToken(OAuthToken $requestToken = null, $oauthVerifier = null, array $params = [])
    {
        if (!is_object($requestToken)) {
            $requestToken = $this->getState('requestToken');
            if (!is_object($requestToken)) {
                throw new Exception('Request token is required to fetch access token!');
            }
        }
        $this->removeState('requestToken');
        $defaultParams = [
            'oauth_consumer_key' => $this->consumerKey,
            'oauth_token' => $requestToken->getToken()
        ];
        if ($oauthVerifier === null) {
            if (isset($_REQUEST['oauth_verifier'])) {
                $oauthVerifier = $_REQUEST['oauth_verifier'];
            }
        }
        if (!empty($oauthVerifier)) {
            $defaultParams['oauth_verifier'] = $oauthVerifier;
        }
        $response = $this->sendSignedRequest($this->accessTokenMethod, $this->accessTokenUrl, array_merge($defaultParams, $params));

        $token = $this->createToken([
            'params' => $response
        ]);
        $this->setAccessToken($token);

        return $token;
    }

    /**
     * Sends HTTP request, signed by [[signatureMethod]].
     * @param string $method request type.
     * @param string $url request URL.
     * @param array $params request params.
     * @param array $headers additional request headers.
     * @return array response.
     */
    protected function sendSignedRequest($method, $url, array $params = [], array $headers = [])
    {
        $params = array_merge($params, $this->generateCommonRequestParams());
        $params = $this->signRequest($method, $url, $params);

        return $this->sendRequest($method, $url, $params, $headers);
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
                if (!empty($params)) {
                    $curlOptions[CURLOPT_POSTFIELDS] = $params;
                }
                $authorizationHeader = $this->composeAuthorizationHeader($params);
                if (!empty($authorizationHeader)) {
                    $curlOptions[CURLOPT_HTTPHEADER] = ['Content-Type: application/atom+xml', $authorizationHeader];
                }
                break;
            }
            case 'HEAD': {
                $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
                if (!empty($params)) {
                    $curlOptions[CURLOPT_URL] = $this->composeUrl($url, $params);
                }
                break;
            }
            default: {
                $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
                if (!empty($params)) {
                    $curlOptions[CURLOPT_POSTFIELDS] = $params;
                }
            }
        }

        return $curlOptions;
    }

    /**
     * @inheritdoc
     */
    protected function apiInternal($accessToken, $url, $method, array $params, array $headers)
    {
        $params['oauth_consumer_key'] = $this->consumerKey;
        $params['oauth_token'] = $accessToken->getToken();
        $response = $this->sendSignedRequest($method, $url, $params, $headers);

        return $response;
    }

    /**
     * Gets new auth token to replace expired one.
     * @param OAuthToken $token expired auth token.
     * @return OAuthToken new auth token.
     */
    public function refreshAccessToken(OAuthToken $token)
    {
        // @todo
        return null;
    }

    /**
     * Composes default [[returnUrl]] value.
     * @return string return URL.
     */
    protected function defaultReturnUrl()
    {
        $params = $_GET;
        unset($params['oauth_token']);
        $params[0] = Yii::$app->controller->getRoute();

        return Yii::$app->getUrlManager()->createAbsoluteUrl($params);
    }

    /**
     * Generates nonce value.
     * @return string nonce value.
     */
    protected function generateNonce()
    {
        return md5(microtime() . mt_rand());
    }

    /**
     * Generates timestamp.
     * @return integer timestamp.
     */
    protected function generateTimestamp()
    {
        return time();
    }

    /**
     * Generate common request params like version, timestamp etc.
     * @return array common request params.
     */
    protected function generateCommonRequestParams()
    {
        $params = [
            'oauth_version' => $this->version,
            'oauth_nonce' => $this->generateNonce(),
            'oauth_timestamp' => $this->generateTimestamp(),
        ];

        return $params;
    }

    /**
     * Sign request with [[signatureMethod]].
     * @param string $method request method.
     * @param string $url request URL.
     * @param array $params request params.
     * @return array signed request params.
     */
    protected function signRequest($method, $url, array $params)
    {
        $signatureMethod = $this->getSignatureMethod();
        $params['oauth_signature_method'] = $signatureMethod->getName();
        $signatureBaseString = $this->composeSignatureBaseString($method, $url, $params);
        $signatureKey = $this->composeSignatureKey();
        $params['oauth_signature'] = $signatureMethod->generateSignature($signatureBaseString, $signatureKey);

        return $params;
    }

    /**
     * Creates signature base string, which will be signed by [[signatureMethod]].
     * @param string $method request method.
     * @param string $url request URL.
     * @param array $params request params.
     * @return string base signature string.
     */
    protected function composeSignatureBaseString($method, $url, array $params)
    {
        unset($params['oauth_signature']);
        uksort($params, 'strcmp'); // Parameters are sorted by name, using lexicographical byte value ordering. Ref: Spec: 9.1.1
        $parts = [
            strtoupper($method),
            $url,
            http_build_query($params, '', '&', PHP_QUERY_RFC3986)
        ];
        $parts = array_map('rawurlencode', $parts);

        return implode('&', $parts);
    }

    /**
     * Composes request signature key.
     * @return string signature key.
     */
    protected function composeSignatureKey()
    {
        $signatureKeyParts = [
            $this->consumerSecret
        ];
        $accessToken = $this->getAccessToken();
        if (is_object($accessToken)) {
            $signatureKeyParts[] = $accessToken->getTokenSecret();
        } else {
            $signatureKeyParts[] = '';
        }
        $signatureKeyParts = array_map('rawurlencode', $signatureKeyParts);

        return implode('&', $signatureKeyParts);
    }

    /**
     * Composes authorization header content.
     * @param array $params request params.
     * @param string $realm authorization realm.
     * @return string authorization header content.
     */
    protected function composeAuthorizationHeader(array $params, $realm = '')
    {
        $header = 'Authorization: OAuth';
        $headerParams = [];
        if (!empty($realm)) {
            $headerParams[] = 'realm="' . rawurlencode($realm) . '"';
        }
        foreach ($params as $key => $value) {
            if (substr_compare($key, 'oauth', 0, 5)) {
                continue;
            }
            $headerParams[] = rawurlencode($key) . '="' . rawurlencode($value) . '"';
        }
        if (!empty($headerParams)) {
            $header .= ' ' . implode(', ', $headerParams);
        }

        return $header;
    }
}
