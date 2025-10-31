<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\filters\auth;

use yii\base\Component;

/**
 * HttpHeaderAuth is an action filter that supports HTTP authentication through HTTP Headers.
 *
 * You may use HttpHeaderAuth by attaching it as a behavior to a controller or module, like the following:
 *
 * ```
 * public function behaviors()
 * {
 *     return [
 *         'basicAuth' => [
 *             'class' => \yii\filters\auth\HttpHeaderAuth::class,
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
 *
 * @template T of Component
 * @extends AuthMethod<T>
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
            if ($this->pattern !== null) {
                if (preg_match($this->pattern, $authHeader, $matches)) {
                    $authHeader = $matches[1];
                } else {
                    return null;
                }
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
