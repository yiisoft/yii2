<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;

/**
 * Facebook allows authentication via Facebook OAuth.
 * In order to use Facebook OAuth you must register your application at [[https://developers.facebook.com/apps]].
 *
 * @see https://developers.facebook.com/apps
 * @see http://developers.facebook.com/docs/reference/api
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Facebook extends OAuth2
{
	/**
	 * @inheritdoc
	 */
	public $authUrl = 'https://www.facebook.com/dialog/oauth';
	/**
	 * @inheritdoc
	 */
	public $tokenUrl = 'https://graph.facebook.com/oauth/access_token';
	/**
	 * @inheritdoc
	 */
	public $apiBaseUrl = 'https://graph.facebook.com';
	/**
	 * @inheritdoc
	 */
	public $scope = 'email';

	/**
	 * @inheritdoc
	 */
	protected function initUserAttributes()
	{
		return $this->api('me', 'GET');
	}
}