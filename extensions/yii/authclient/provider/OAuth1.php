<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\provider;

use Yii;

/**
 * Class OAuth1
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class OAuth1 extends \yii\authclient\OAuth1 implements ProviderInterface
{
	use ProviderTrait;

	/**
	 * @inheritdoc
	 */
	public function authenticate()
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
			$requestToken = $this->fetchRequestToken();
			// Get authorization URL.
			$url = $this->buildAuthUrl($requestToken);
			// Redirect to authorization URL.
			return Yii::$app->getResponse()->redirect($url);
		} else {
			// Upgrade to access token.
			$accessToken = $this->fetchAccessToken();
			$this->isAuthenticated = true;
		}

		return $this->isAuthenticated;
	}
}