<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;

/**
 * GitHub allows authentication via GitHub OAuth.
 *
 * In order to use GitHub OAuth you must register your application at <https://github.com/settings/applications/new>.
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'github' => [
 *                 'class' => 'yii\authclient\clients\GitHub',
 *                 'clientId' => 'github_client_id',
 *                 'clientSecret' => 'github_client_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see http://developer.github.com/v3/oauth/
 * @see https://github.com/settings/applications/new
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class GitHub extends OAuth2
{
	/**
	 * @inheritdoc
	 */
	public $authUrl = 'https://github.com/login/oauth/authorize';
	/**
	 * @inheritdoc
	 */
	public $tokenUrl = 'https://github.com/login/oauth/access_token';
	/**
	 * @inheritdoc
	 */
	public $apiBaseUrl = 'https://api.github.com';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if ($this->scope === null) {
			$this->scope = implode(' ', [
				'user',
				'user:email',
			]);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function initUserAttributes()
	{
		return $this->api('user', 'GET');
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultName()
	{
		return 'github';
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultTitle()
	{
		return 'GitHub';
	}
}
