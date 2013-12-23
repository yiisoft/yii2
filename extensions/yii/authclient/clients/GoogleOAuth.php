<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;

/**
 * GoogleOAuth allows authentication via Google OAuth.
 * In order to use Google OAuth you must register your application at [[https://code.google.com/apis/console#access]].
 *
 * @see https://code.google.com/apis/console#access
 * @see https://developers.google.com/google-apps/contacts/v3/
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class GoogleOAuth extends OAuth2
{
	/**
	 * @inheritdoc
	 */
	public $authUrl = 'https://accounts.google.com/o/oauth2/auth';
	/**
	 * @inheritdoc
	 */
	public $tokenUrl = 'https://accounts.google.com/o/oauth2/token';
	/**
	 * @inheritdoc
	 */
	public $apiBaseUrl = 'https://www.googleapis.com/oauth2/v1';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		if ($this->scope === null) {
			$this->scope = implode(' ', [
				'https://www.googleapis.com/auth/userinfo.profile',
				'https://www.googleapis.com/auth/userinfo.email',
			]);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function initUserAttributes()
	{
		$attributes = $this->api('userinfo', 'GET');
		return $attributes;
	}
}