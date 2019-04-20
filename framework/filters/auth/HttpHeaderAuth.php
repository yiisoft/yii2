<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters\auth;

/**
 * HttpHeaderAuth 是通过 HTTP 头支持 HTTP 身份验证的操作筛选器。
 *
 * 您可以使用 HttpHeaderAuth，方法是将其作为行为附加到控制器或模块，如下所示:
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
 * HttpHeaderAuth 默认使用了 [[\yii\web\User::loginByAccessToken()|loginByAccessToken()]]
 * `user` 应用程序组件的方法并传递 `X-Api-Key` 头的值。
 * 此实现用于验证 API 客户端。
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
     * @var string 用于提取 HTTP 身份验证值的模式
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
