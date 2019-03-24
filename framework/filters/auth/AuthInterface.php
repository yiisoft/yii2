<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters\auth;

use yii\web\IdentityInterface;
use yii\web\Request;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use yii\web\User;

/**
 * AuthInterface 是应该由 Auth 方法类实现的接口。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface AuthInterface
{
    /**
     * 对当前用户进行身份验证。
     * @param User $user
     * @param Request $request
     * @param Response $response
     * @return IdentityInterface 已验证的用户标识。如果不提供身份验证信息，则返回空。
     * @throws UnauthorizedHttpException 如果提供的身份验证信息无效。
     */
    public function authenticate($user, $request, $response);

    /**
     * 在身份验证失败时产生质询。
     * 例如，一些适当的 HTTP headers 可能会生成。
     * @param Response $response
     */
    public function challenge($response);

    /**
     * 处理身份验证失败。
     * 该实现通常应抛出未经授权的 UnauthorizedHttpException 异常以指示身份验证失败。
     * @param Response $response
     * @throws UnauthorizedHttpException
     */
    public function handleFailure($response);
}
