<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth1;

/**
 * Twitter allows authentication via Twitter OAuth.
 * In order to use Twitter OAuth you must register your application at [[https://dev.twitter.com/apps/new]].
 *
 * @see https://dev.twitter.com/apps/new
 * @see https://dev.twitter.com/docs/api
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Twitter extends OAuth1
{
	/**
	 * @inheritdoc
	 */
	public $authUrl = 'https://api.twitter.com/oauth/authorize';
	/**
	 * @inheritdoc
	 */
	public $requestTokenUrl = 'https://api.twitter.com/oauth/request_token';
	/**
	 * @inheritdoc
	 */
	public $requestTokenMethod = 'POST';
	/**
	 * @inheritdoc
	 */
	public $accessTokenUrl = 'https://api.twitter.com/oauth/access_token';
	/**
	 * @inheritdoc
	 */
	public $accessTokenMethod = 'POST';
	/**
	 * @inheritdoc
	 */
	public $apiBaseUrl = 'https://api.twitter.com/1.1';

	/**
	 * @inheritdoc
	 */
	protected function initUserAttributes()
	{
		return $this->api('account/verify_credentials.json', 'GET');
	}
}