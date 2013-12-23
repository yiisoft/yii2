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
	public function __construct($config = [])
	{
		$config = array_merge(
			[
				'consumerKey' => 'anonymous',
				'consumerSecret' => 'anonymous',
				'requestTokenUrl' => 'https://api.twitter.com/oauth/request_token',
				'requestTokenMethod' => 'POST',
				'accessTokenUrl' => 'https://api.twitter.com/oauth/access_token',
				'accessTokenMethod' => 'POST',
				'authUrl' => 'https://api.twitter.com/oauth/authorize',
				'scope' => '',
				'apiBaseUrl' => 'https://api.twitter.com/1.1',
			],
			$config
		);
		parent::__construct($config);
	}

	/**
	 * @inheritdoc
	 */
	protected function initUserAttributes()
	{
		return $this->api('account/verify_credentials.json', 'GET');
	}
}