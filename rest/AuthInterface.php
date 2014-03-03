<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use yii\web\User;
use yii\web\Request;
use yii\web\Response;
use yii\web\IdentityInterface;
use yii\web\UnauthorizedHttpException;

/**
 * AuthInterface is the interface required by classes than support user authentication.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface AuthInterface
{
	/**
	 * Authenticates the current user.
	 *
	 * @param User $user
	 * @param Request $request
	 * @param Response $response
	 * @return IdentityInterface the authenticated user identity. If authentication information is not provided, null will be returned.
	 * @throws UnauthorizedHttpException if authentication information is provided but is invalid.
	 */
	public function authenticate($user, $request, $response);
	/**
	 * Handles authentication failure.
	 * The implementation should normally throw UnauthorizedHttpException to indicate authentication failure.
	 * @param Response $response
	 * @throws UnauthorizedHttpException
	 */
	public function handleFailure($response);
}
