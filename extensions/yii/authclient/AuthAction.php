<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient;

use yii\base\Action;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use Yii;

/**
 * Class AuthAction
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class AuthAction extends Action
{
	/**
	 * @var string name of the auth client collection application component.
	 */
	public $clientCollection = 'auth';
	/**
	 * @var string name of the GET param, which is used to passed auth client id to this action.
	 */
	public $clientIdGetParamName = 'authclient';
	/**
	 * @var callable PHP callback, which should be triggered in case of successful authentication.
	 */
	public $successCallback;
	/**
	 * @var string the redirect url after successful authorization.
	 */
	private $_successUrl = '';
	/**
	 * @var string the redirect url after unsuccessful authorization (e.g. user canceled).
	 */
	private $_cancelUrl = '';

	/**
	 * @param string $url successful URL.
	 */
	public function setSuccessUrl($url)
	{
		$this->_successUrl = $url;
	}

	/**
	 * @return string successful URL.
	 */
	public function getSuccessUrl()
	{
		if (empty($this->_successUrl)) {
			$this->_successUrl = $this->defaultSuccessUrl();
		}
		return $this->_successUrl;
	}

	/**
	 * @param string $url cancel URL.
	 */
	public function setCancelUrl($url)
	{
		$this->_cancelUrl = $url;
	}

	/**
	 * @return string cancel URL.
	 */
	public function getCancelUrl()
	{
		if (empty($this->_cancelUrl)) {
			$this->_cancelUrl = $this->defaultCancelUrl();
		}
		return $this->_cancelUrl;
	}

	/**
	 * Creates default {@link successUrl} value.
	 * @return string success URL value.
	 */
	protected function defaultSuccessUrl()
	{
		return Yii::$app->getUser()->getReturnUrl();
	}

	/**
	 * Creates default {@link cancelUrl} value.
	 * @return string cancel URL value.
	 */
	protected function defaultCancelUrl()
	{
		return Yii::$app->getRequest()->getAbsoluteUrl();
	}

	/**
	 * Runs the action.
	 */
	public function run()
	{
		if (!empty($_GET[$this->clientIdGetParamName])) {
			$clientId = $_GET[$this->clientIdGetParamName];
			/** @var \yii\authclient\Collection $collection */
			$collection = Yii::$app->getComponent($this->clientCollection);
			if (!$collection->hasClient($clientId)) {
				throw new NotFoundHttpException("Unknown auth client '{$clientId}'");
			}
			$client = $collection->getClient($clientId);
			return $this->auth($client);
		} else {
			throw new NotFoundHttpException();
		}
	}

	/**
	 * @param mixed $client auth client instance.
	 * @return \yii\web\Response response instance.
	 * @throws \yii\base\NotSupportedException on invalid client.
	 */
	protected function auth($client)
	{
		if ($client instanceof OpenId) {
			return $this->authOpenId($client);
		} elseif ($client instanceof OAuth2) {
			return $this->authOAuth2($client);
		} elseif ($client instanceof OAuth1) {
			return $this->authOAuth1($client);
		} else {
			throw new NotSupportedException('Provider "' . get_class($client) . '" is not supported.');
		}
	}

	/**
	 * @param mixed $provider
	 * @return \yii\web\Response
	 */
	protected function authSuccess($provider)
	{
		call_user_func($this->successCallback, $provider);
		return $this->redirectSuccess();
	}

	/**
	 * Redirect to the given URL or simply close the popup window.
	 * @param mixed $url URL to redirect, could be a string or array config to generate a valid URL.
	 * @param boolean $enforceRedirect indicates if redirect should be performed even in case of popup window.
	 * @return \yii\web\Response response instance.
	 */
	public function redirect($url, $enforceRedirect = true)
	{
		$viewData = [
			'url' => $url,
			'enforceRedirect' => $enforceRedirect,
		];
		$viewFile = __DIR__ . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'redirect.php';

		$response = Yii::$app->getResponse();
		$response->content = Yii::$app->getView()->renderFile($viewFile, $viewData);
		return $response;
	}

	/**
	 * Redirect to the URL. If URL is null, {@link successUrl} will be used.
	 * @param string $url URL to redirect.
	 * @return \yii\web\Response response instance.
	 */
	public function redirectSuccess($url = null)
	{
		if ($url === null) {
			$url = $this->getSuccessUrl();
		}
		return $this->redirect($url);
	}

	/**
	 * Redirect to the {@link cancelUrl} or simply close the popup window.
	 * @param string $url URL to redirect.
	 * @return \yii\web\Response response instance.
	 */
	public function redirectCancel($url = null)
	{
		if ($url === null) {
			$url = $this->getCancelUrl();
		}
		return $this->redirect($url, false);
	}

	/**
	 * @param OpenId $provider provider instance.
	 * @return \yii\web\Response action response.
	 * @throws Exception on failure
	 * @throws \yii\web\HttpException
	 */
	protected function authOpenId($provider)
	{
		if (!empty($_REQUEST['openid_mode'])) {
			switch ($_REQUEST['openid_mode']) {
				case 'id_res':
					if ($provider->validate()) {
						$attributes = array(
							'id' => $provider->identity
						);
						$rawAttributes = $provider->getAttributes();
						foreach ($provider->requiredAttributes as $openIdAttributeName) {
							if (isset($rawAttributes[$openIdAttributeName])) {
								$attributes[$openIdAttributeName] = $rawAttributes[$openIdAttributeName];
							} else {
								throw new Exception('Unable to complete the authentication because the required data was not received.');
							}
						}
						$provider->setUserAttributes($attributes);
						return $this->authSuccess($provider);
					} else {
						throw new Exception('Unable to complete the authentication because the required data was not received.');
					}
					break;
				case 'cancel':
					$this->redirectCancel();
					break;
				default:
					throw new HttpException(400);
					break;
			}
		} else {
			//$provider->identity = $provider->authUrl; // Setting identifier
			$request = Yii::$app->getRequest();
			$provider->realm = $request->getHostInfo();
			$provider->returnUrl = $provider->realm . $request->getUrl(); // getting return URL

			$url = $provider->authUrl();
			return Yii::$app->getResponse()->redirect($url);
		}
		return $this->redirectCancel();
	}

	/**
	 * @param OAuth1 $provider
	 * @return \yii\web\Response
	 */
	protected function authOAuth1($provider)
	{
		// user denied error
		if (isset($_GET['denied'])) {
			return $this->redirectCancel();
		}

		if (isset($_REQUEST['oauth_token'])) {
			$oauthToken = $_REQUEST['oauth_token'];
		}

		if (!isset($oauthToken)) {
			// Get request token.
			$requestToken = $provider->fetchRequestToken();
			// Get authorization URL.
			$url = $provider->buildAuthUrl($requestToken);
			// Redirect to authorization URL.
			return Yii::$app->getResponse()->redirect($url);
		} else {
			// Upgrade to access token.
			$accessToken = $provider->fetchAccessToken();
			return $this->authSuccess($provider);
		}
	}

	/**
	 * @param OAuth2 $provider
	 * @return \yii\web\Response
	 * @throws \yii\base\Exception
	 */
	protected function authOAuth2($provider)
	{
		if (isset($_GET['error'])) {
			if ($_GET['error'] == 'access_denied') {
				// user denied error
				return $this->redirectCancel();
			} else {
				// request error
				if (isset($_GET['error_description'])) {
					$errorMessage = $_GET['error_description'];
				} elseif (isset($_GET['error_message'])) {
					$errorMessage = $_GET['error_message'];
				} else {
					$errorMessage = http_build_query($_GET);
				}
				throw new Exception('Auth error: ' . $errorMessage);
			}
		}

		// Get the access_token and save them to the session.
		if (isset($_GET['code'])) {
			$code = $_GET['code'];
			$token = $provider->fetchAccessToken($code);
			if (!empty($token)) {
				return $this->authSuccess($provider);
			} else {
				return $this->redirectCancel();
			}
		} else {
			$url = $provider->buildAuthUrl();
			return Yii::$app->getResponse()->redirect($url);
		}
	}
}