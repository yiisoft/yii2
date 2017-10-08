<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\filters\auth;

use Yii;
use yii\filters\auth\HttpBasicAuth;
use yiiunit\framework\filters\stubs\UserIdentity;

/**
 * @group filters
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.7
 */
class BasicAuthTest extends AuthTest
{
    /**
     * @dataProvider tokenProvider
     * @param string|null $token
     * @param string|null $login
     */
    public function testHttpBasicAuth($token, $login)
    {
        $_SERVER['PHP_AUTH_USER'] = $token;
        $_SERVER['PHP_AUTH_PW'] = 'whatever, we are testers';
        $filter = ['class' => HttpBasicAuth::className()];
        $this->ensureFilterApplies($token, $login, $filter);
    }

    /**
     * @dataProvider tokenProvider
     * @param string|null $token
     * @param string|null $login
     */
    public function testHttpBasicAuthWithHttpAuthorizationHeader($token, $login)
    {
        Yii::$app->request->headers->set('HTTP_AUTHORIZATION', 'Basic ' . base64_encode($token . ':' . 'mypw'));
        $filter = ['class' => HttpBasicAuth::className()];
        $this->ensureFilterApplies($token, $login, $filter);
    }

    /**
     * @dataProvider tokenProvider
     * @param string|null $token
     * @param string|null $login
     */
    public function testHttpBasicAuthWithRedirectHttpAuthorizationHeader($token, $login)
    {
        Yii::$app->request->headers->set('REDIRECT_HTTP_AUTHORIZATION', 'Basic ' . base64_encode($token . ':' . 'mypw'));
        $filter = ['class' => HttpBasicAuth::className()];
        $this->ensureFilterApplies($token, $login, $filter);
    }

    /**
     * @dataProvider tokenProvider
     * @param string|null $token
     * @param string|null $login
     */
    public function testHttpBasicAuthCustom($token, $login)
    {
        $_SERVER['PHP_AUTH_USER'] = $login;
        $_SERVER['PHP_AUTH_PW'] = 'whatever, we are testers';
        $filter = [
            'class' => HttpBasicAuth::className(),
            'auth' => function ($username, $password) {
                if (preg_match('/\d$/', $username)) {
                    return UserIdentity::findIdentity($username);
                }

                return null;
            },
        ];
        $this->ensureFilterApplies($token, $login, $filter);
    }

    public function authMethodProvider()
    {
        return [
            ['yii\filters\auth\HttpBasicAuth'],
        ];
    }
}
