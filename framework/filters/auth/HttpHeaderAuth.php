<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters\auth;

/**
 * HttpHeaderAuth is an action filter that supports HTTP authentication through HTTP Headers.
 *
 * You may use HttpHeaderAuth by attaching it as a behavior to a controller or module, like the following:
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'basicAuth' => [
 *             'class' => \yii\filters\auth\HttpHeaderAuth::className(),
 *         ],
 *     ];
 * }
 * ```
 *
 * The default implementation of HttpHeaderAuth uses the [[\yii\web\User::loginByAccessToken()|loginByAccessToken()]]
 * method of the `user` application component and passes the value of the `X-Api-Key` header. This implementation is used
 * for authenticating API clients.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Benoît Boure <benoit.boure@gmail.com>
 * @since 2.0.14
 */
class HttpHeaderAuth extends AuthMethod
{
    /**
     * @var string the HTTP header name
     */
    public $header = 'X-Api-Key';
    /**
     * @var string a pattern to use to extract the HTTP authentication value
     */
    public $pattern;


    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get($this->header);

        if ($authHeader !== null) {
            if ($this->pattern !== null && preg_match($this->pattern, $authHeader, $matches)) {
                $authHeader = $matches[1];
            }
            $identity = $user->loginByAccessToken($authHeader, get_class($this));
            if ($identity === null) {
                $this->challenge($response);
                $this->handleFailure($response);
            }

            return $identity;
        }

        return null;
    }
}
