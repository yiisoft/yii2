<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\provider;

use Yii;
use yii\base\Exception;
use yii\web\HttpException;

/**
 * Class OpenId
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class OpenId extends \yii\authclient\OpenId implements ProviderInterface
{
	use ProviderTrait;

	/**
	 * @inheritdoc
	 */
	public function authenticate()
	{
		if (!empty($_REQUEST['openid_mode'])) {
			switch ($_REQUEST['openid_mode']) {
				case 'id_res':
					if ($this->validate()) {
						$attributes = array(
							'id' => $this->identity
						);
						$rawAttributes = $this->getAttributes();
						foreach ($this->getRequiredAttributes() as $openIdAttributeName) {
							if (isset($rawAttributes[$openIdAttributeName])) {
								$attributes[$openIdAttributeName] = $rawAttributes[$openIdAttributeName];
							} else {
								throw new Exception('Unable to complete the authentication because the required data was not received.');
							}
						}
						$this->setAttributes($attributes);
						$this->isAuthenticated = true;
						return true;
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
			$this->identity = $this->authUrl; // Setting identifier
			$this->required = []; // Try to get info from openid provider
			foreach ($this->getRequiredAttributes() as $openIdAttributeName) {
				$this->required[] = $openIdAttributeName;
			}
			$request = Yii::$app->getRequest();
			$this->realm = $request->getHostInfo();
			$this->returnUrl = $this->realm . $request->getUrl(); // getting return URL

			$url = $this->authUrl();
			return Yii::$app->getResponse()->redirect($url);
		}

		return false;
	}
}