<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters\auth;

use yii\web\Request;

/**
 * HttpBasicAuth is an action filter that supports the HTTP Basic authentication method.
 *
 * You may use HttpBasicAuth by attaching it as a behavior to a controller or module, like the following:
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
 * The default implementation of HttpBasicAuth uses the [[\yii\web\User::loginByAccessToken()|loginByAccessToken()]]
 * method of the `user` application component and only passes the user name. This implementation is used
 * for authenticating API clients.
 *
 * If you want to authenticate users using username and password, you should provide the [[auth]] function for example like the following:
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
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HttpBasicAuth extends AuthMethod
{
    /**
     * @var string the HTTP authentication realm
     */
    public $realm = 'api';
    /**
     * @var callable a PHP callable that will authenticate the user with the HTTP basic auth information.
     * The callable receives a username and a password as its parameters. It should return an identity object
     * that matches the username and password. Null should be returned if there is no such identity.
     *
     * The following code is a typical implementation of this callable:
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
     * If this property is not set, the username information will be considered as an access token
     * while the password information will be ignored. The [[\yii\web\User::loginByAccessToken()]]
     * method will be called to authenticate and login the user.
     */
    public $auth;


    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        list($username, $password) = $this->getCredentialsFromRequest($request);

        if ($this->auth) {
            if ($username !== null || $password !== null) {
                $identity = call_user_func($this->auth, $username, $password);
                if ($identity !== null) {
                    $user->switchIdentity($identity);
                } else {
                    $this->handleFailure($response);
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
     * Extract username and password from $request.
     *
     * @param Request $request
     * @since 2.0.13
     * @return array
     */
    protected function getCredentialsFromRequest($request)
    {
        $username = $request->getAuthUser();
        $password = $request->getAuthPassword();

        if ($username !== null || $password !== null) {
            return [$username, $password];
        }

        $headers = $request->getHeaders();
        $auth_token = $headers->get('HTTP_AUTHORIZATION') ?: $headers->get('REDIRECT_HTTP_AUTHORIZATION');
        if ($auth_token != null && strpos(strtolower($auth_token), 'basic') === 0) {
            $parts = array_map(function ($value) {
                return strlen($value) === 0 ? null : $value;
            }, explode(':', base64_decode(mb_substr($auth_token, 6)), 2));

            if (count($parts) < 2) {
                return [$parts[0], null];
            }

            return $parts;
        }

        return [null, null];
    }

    /**
     * @inheritdoc
     */
    public function challenge($response)
    {
        $response->getHeaders()->set('WWW-Authenticate', "Basic realm=\"{$this->realm}\"");
    }
}
