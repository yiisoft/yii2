<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\provider;

use yii\authclient\openid\Client;

/**
 * Class OpenId
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class OpenId extends Client implements ProviderInterface
{
	use ProviderTrait;

	/**
	 * Authenticate the user.
	 * @return boolean whether user was successfully authenticated.
	 */
	public function authenticate()
	{
		// TODO: Implement authenticate() method.
	}
}