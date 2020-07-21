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
use yii\base\Event;
use yii\web\User;

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

    /**
     * This tests checks, that:
     *  - HttpBasicAuth does not call `auth` closure, when user is already authenticated
     *  - HttpBasicAuth does not switch identity, when the user identity to be set is the same as current user's one
     *
     * @dataProvider tokenProvider
     * @param string|null $token
     * @param string|null $login
     */
    public function testHttpBasicAuthIssue15658($token, $login)
    {
        $_SERVER['PHP_AUTH_USER'] = $login;
        $_SERVER['PHP_AUTH_PW'] = 'y0u7h1nk175r34l?';

        // Login user and set fake identity ID to session
        if ($login !== null) {
            Yii::$app->user->login(UserIdentity::findIdentity($login));
        }

        $session = Yii::$app->session;
        $idParam = Yii::$app->user->idParam;
        $idValue = 'should not be changed';
        $session->set($idParam, $idValue);

        $filter = [
            'class' => HttpBasicAuth::className(),
            'auth' => function ($username, $password) {
                if ($username !== null) {
                    $this->fail('Authentication closure should not be called when user is already authenticated');
                }
                return null;
            },
        ];
        $this->ensureFilterApplies($token, $login, $filter);

        $this->assertSame($idValue, $session->get($idParam));
        $session->destroy();
    }

    public function authMethodProvider()
    {
        return [
            ['yii\filters\auth\HttpBasicAuth'],
        ];
    }

    /**
     * @dataProvider tokenProvider
     * @param string|null $token
     * @param string|null $login
     */
    public function testAfterLoginEventIsTriggered18031($token, $login)
    {
        $triggered = false;
        Event::on('\yii\web\User', User::EVENT_AFTER_LOGIN, function ($event) use (&$triggered) {
            $triggered = true;
            $this->assertTrue($triggered);
        });
        $this->testHttpBasicAuthCustom($token, $login);
        Event::off('\yii\web\User', User::EVENT_AFTER_LOGIN); // required because this method runs in foreach loop. See @dataProvider tokenProvider
    }
}
