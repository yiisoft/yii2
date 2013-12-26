<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;
use yii\web\HttpException;
use Yii;

/**
 * LinkedIn allows authentication via LinkedIn OAuth.
 * In order to use linkedIn OAuth you must register your application at [[https://www.linkedin.com/secure/developer]].
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'linkedin' => [
 *                 'class' => 'yii\authclient\clients\LinkedIn',
 *                 'clientId' => 'linkedin_client_id',
 *                 'clientSecret' => 'linkedin_client_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see http://developer.linkedin.com/documents/authentication
 * @see https://www.linkedin.com/secure/developer
 * @see http://developer.linkedin.com/apis
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class LinkedIn extends OAuth2
{
	/**
	 * @inheritdoc
	 */
	public $authUrl = 'https://www.linkedin.com/uas/oauth2/authorization';
	/**
	 * @inheritdoc
	 */
	public $tokenUrl = 'https://www.linkedin.com/uas/oauth2/accessToken';
	/**
	 * @inheritdoc
	 */
	public $apiBaseUrl = 'https://api.linkedin.com/v1';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if ($this->scope === null) {
			$this->scope = implode(' ', [
				'r_basicprofile',
				'r_emailaddress',
			]);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultNormalizeUserAttributeMap()
	{
		return [
			'email' => 'email-address',
			'first_name' => 'first-name',
			'last_name' => 'last-name',
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function initUserAttributes()
	{
		$attributeNames = [
			'id',
			'email-address',
			'first-name',
			'last-name',
			'public-profile-url',
		];
		return $this->api('people/~:(' . implode(',', $attributeNames) . ')', 'GET');
	}

	/**
	 * @inheritdoc
	 */
	public function buildAuthUrl(array $params = [])
	{
		$authState = $this->generateAuthState();
		$this->setState('authState', $authState);
		$params['state'] = $authState;
		return parent::buildAuthUrl($params);
	}

	/**
	 * @inheritdoc
	 */
	public function fetchAccessToken($authCode, array $params = [])
	{
		$authState = $this->getState('authState');
		if (!isset($_REQUEST['state']) || empty($authState) || strcmp($_REQUEST['state'], $authState) !== 0) {
			throw new HttpException(400, 'Invalid auth state parameter.');
		} else {
			$this->removeState('authState');
		}
		return parent::fetchAccessToken($authCode, $params);
	}

	/**
	 * @inheritdoc
	 */
	protected function apiInternal($accessToken, $url, $method, array $params)
	{
		$params['oauth2_access_token'] = $accessToken->getToken();
		return $this->sendRequest($method, $url, $params);
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultReturnUrl()
	{
		$params = $_GET;
		unset($params['code']);
		unset($params['state']);
		return Yii::$app->getUrlManager()->createAbsoluteUrl(Yii::$app->controller->getRoute(), $params);
	}

	/**
	 * Generates the auth state value.
	 * @return string auth state value.
	 */
	protected function generateAuthState() {
		return sha1(uniqid(get_class($this), true));
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultName()
	{
		return 'linkedin';
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultTitle()
	{
		return 'LinkedIn';
	}
}