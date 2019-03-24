<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters\auth;

/**
 * HttpBasicAuth 是支持 HTTP 基本身份验证方法的操作筛选器。
 *
 * 您可以通过将 HttpBasicAuth 作为行为附加到控制器或模块，像下面这样：
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'basicAuth' => [
 *             'class' => \yii\filters\auth\HttpBasicAuth::className(),
 *         ],
 *     ];
 * }
 * ```
 *
 * HttpBasicAuth 默认实现使用 [[\yii\web\User::loginByAccessToken()|loginByAccessToken()]] 方法。
 * `user` 应用程序组件的方法只传递用户名。
 * 此实现用于对 API 客户端进行身份验证。
 *
 * 如果要使用用户名和密码对用户进行身份验证，您应该提供 [[auth]] 功能例如：
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'basicAuth' => [
 *             'class' => \yii\filters\auth\HttpBasicAuth::className(),
 *             'auth' => function ($username, $password) {
 *                 $user = User::find()->where(['username' => $username])->one();
 *                 if ($user->verifyPassword($password)) {
 *                     return $user;
 *                 }
 *                 return null;
 *             },
 *         ],
 *     ];
 * }
 * ```
 *
 * > Tip: 如果身份验证不能按预期工作，确保您的 Web 服务器通过
 * `$_SERVER['PHP_AUTH_USER']` 和 `$_SERVER['PHP_AUTH_PW']` 的值。
 * 如果你使用 Apache 配合 PHP-CGI，您可能需要将此行添加到 `.htaccess` 文件中：
 * ```
 * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HttpBasicAuth extends AuthMethod
{
    /**
     * @var string  HTTP 身份验证范围
     */
    public $realm = 'api';
    /**
     * @var callable 可调用 PHP 将使用 HTTP 基本身份验证信息对用户进行身份验证。
     * 可调用文件接收用户名和密码作为其参数。它应该返回一个标识对象。
     * 与用户名和密码匹配的，如果没有此类标识则应返回空值。
     * 仅当当前用户未通过身份验证时才调用可调用。
     *
     * 以下代码是此可调用的典型实现：
     *
     * ```php
     * function ($username, $password) {
     *     return \app\models\User::findOne([
     *         'username' => $username,
     *         'password' => $password,
     *     ]);
     * }
     * ```
     *
     * 如果未设置此属性，则用户名信息将被视为访问令牌。
     * 而密码信息将被忽略。在 [[\yii\web\User::loginByAccessToken()]]
     * 将调用方法对用户进行身份验证和登录。
     */
    public $auth;


    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        list($username, $password) = $request->getAuthCredentials();

        if ($this->auth) {
            if ($username !== null || $password !== null) {
                $identity = $user->getIdentity() ?: call_user_func($this->auth, $username, $password);

                if ($identity === null) {
                    $this->handleFailure($response);
                } elseif ($user->getIdentity(false) !== $identity) {
                    $user->switchIdentity($identity);
                }

                return $identity;
            }
        } elseif ($username !== null) {
            $identity = $user->loginByAccessToken($username, get_class($this));
            if ($identity === null) {
                $this->handleFailure($response);
            }

            return $identity;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function challenge($response)
    {
        $response->getHeaders()->set('WWW-Authenticate', "Basic realm=\"{$this->realm}\"");
    }
}
