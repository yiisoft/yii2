<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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
     *
     * @param string|null $token The token to be used for authentication.
     * @param string|null $login The login of the user that should be authenticated.
     */
    public function testHttpBasicAuth(string|null $token, string|null $login): void
    {
        $original = $_SERVER;

        $_SERVER['PHP_AUTH_USER'] = $token;
        $_SERVER['PHP_AUTH_PW'] = 'whatever, we are testers';
        $filter = ['class' => HttpBasicAuth::class];
        $this->ensureFilterApplies($token, $login, $filter);
        $_SERVER = $original;
    }

    /**
     * @dataProvider tokenProvider
     *
     * @param string|null $token The token to be used for authentication.
     * @param string|null $login The login of the user that should be authenticated.
     */
    public function testHttpBasicAuthWithHttpAuthorizationHeader(string|null $token, string|null $login): void
    {
        $original = $_SERVER;

        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode($token . ':' . 'mypw');
        $filter = ['class' => HttpBasicAuth::class];
        $this->ensureFilterApplies($token, $login, $filter);
        $_SERVER = $original;
    }

    /**
     * @dataProvider tokenProvider
     *
     * @param string|null $token The token to be used for authentication.
     * @param string|null $login The login of the user that should be authenticated.
     */
    public function testHttpBasicAuthWithRedirectHttpAuthorizationHeader(string|null $token, string|null $login): void
    {
        $original = $_SERVER;

        $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode($token . ':' . 'mypw');
        $filter = ['class' => HttpBasicAuth::class];
        $this->ensureFilterApplies($token, $login, $filter);
        $_SERVER = $original;
    }

    /**
     * @dataProvider tokenProvider
     *
     * @param string|null $token The token to be used for authentication.
     * @param string|null $login The login of the user that should be authenticated.
     */
    public function testHttpBasicAuthCustom(string|null $token, string|null $login): void
    {
        $_SERVER['PHP_AUTH_USER'] = $login;
        $_SERVER['PHP_AUTH_PW'] = 'whatever, we are testers';
        $filter = [
            'class' => HttpBasicAuth::class,
            'auth' => function ($username, $password) {
                if (preg_match('/\d$/', (string)$username)) {
                    return UserIdentity::findIdentity($username);
                }

                return null;
            },
        ];
        $this->ensureFilterApplies($token, $login, $filter);
    }

    /**
     * This tests checks, that:
     *  - HttpBasicAuth does not call `auth` closure, when a user is already authenticated
     *  - HttpBasicAuth does not switch identity, even when the user identity to be set is the same as current user's one
     *
     * @dataProvider tokenProvider
     *
     * @param string|null $token The token to be used for authentication.
     * @param string|null $login The login of the user that should be authenticated.
     */
    public function testHttpBasicAuthIssue15658(string|null $token, string|null $login): void
    {
        $_SERVER['PHP_AUTH_USER'] = $login;
        $_SERVER['PHP_AUTH_PW'] = 'y0u7h1nk175r34l?';

        $user = Yii::$app->user;
        $session = Yii::$app->session;
        $user->login(UserIdentity::findIdentity('user1'));
        $identity = $user->getIdentity();
        $sessionId = $session->getId();

        $filter = [
            'class' => HttpBasicAuth::class,
            'auth' => function ($username, $password): void {
                $this->fail('Authentication closure should not be called when user is already authenticated');
            },
        ];
        $this->ensureFilterApplies('token1', 'user1', $filter);

        $this->assertSame($identity, $user->getIdentity());
        $this->assertSame($sessionId, $session->getId());
        $session->destroy();
    }

    public static function authMethodProvider(): array
    {
        return [
            ['yii\filters\auth\HttpBasicAuth'],
        ];
    }

    /**
     * @dataProvider tokenProvider
     *
     * @param string|null $token The token to be used for authentication.
     * @param string|null $login The login of the user that should be authenticated.
     */
    public function testAfterLoginEventIsTriggered18031(string|null $token, string|null $login): void
    {
        $triggered = false;
        Event::on('\yii\web\User', User::EVENT_AFTER_LOGIN, function ($event) use (&$triggered): void {
            $triggered = true;
            $this->assertTrue($triggered);
        });
        $this->testHttpBasicAuthCustom($token, $login);
        Event::off('\yii\web\User', User::EVENT_AFTER_LOGIN); // required because this method runs in foreach loop. See @dataProvider tokenProvider
    }
}
