<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\provider;
use Yii;
use yii\base\Exception;

/**
 * Class OAuth2
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class OAuth2 extends \yii\authclient\OAuth2 implements ProviderInterface
{
	use ProviderTrait;

	/**
	 * @inheritdoc
	 */
	public function authenticate()
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
			$token = $this->fetchAccessToken($code);
			if (!empty($token)) {
				$this->isAuthenticated = true;
			}
		} else {
			$url = $this->buildAuthUrl();
			return Yii::$app->getResponse()->redirect($url);
		}

		return $this->isAuthenticated;
	}
}