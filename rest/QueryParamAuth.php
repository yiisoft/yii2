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
 * QueryParamAuth implements the authentication method based on the access token passed through a query parameter.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class QueryParamAuth extends Component implements AuthInterface
{
	/**
	 * @var string the parameter name for passing the access token
	 */
	public $tokenParam = 'access-token';

	/**
	 * @inheritdoc
	 */
	public function authenticate($user, $request, $response)
	{
		$accessToken = $request->get($this->tokenParam);
		if (is_string($accessToken)) {
			$identity = $user->loginByAccessToken($accessToken);
			if ($identity !== null) {
				return $identity;
			}
		}
		if ($accessToken !== null) {
			$this->handleFailure($response);
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function handleFailure($response)
	{
		throw new UnauthorizedHttpException('You are requesting with an invalid access token.');
	}
}
