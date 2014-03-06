<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use Yii;
use yii\base\Component;
use yii\web\UnauthorizedHttpException;

/**
 * HttpBasicAuth implements the HTTP Basic authentication method.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HttpBasicAuth extends Component implements AuthInterface
{
	/**
	 * @var string the HTTP authentication realm
	 */
	public $realm = 'api';

	/**
	 * @inheritdoc
	 */
	public function authenticate($user, $request, $response)
	{
		if (($accessToken = $request->getAuthUser()) !== null) {
			$identity = $user->loginByAccessToken($accessToken);
			if ($identity !== null) {
				return $identity;
			}
			$this->handleFailure($response);
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function handleFailure($response)
	{
		$response->getHeaders()->set('WWW-Authenticate', "Basic realm=\"{$this->realm}\"");
		throw new UnauthorizedHttpException('You are requesting with an invalid access token.');
	}
}
