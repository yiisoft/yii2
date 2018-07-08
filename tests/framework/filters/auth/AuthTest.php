<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\filters\auth;

use Yii;
use yii\base\Action;
use yii\filters\auth\AuthMethod;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\auth\HttpHeaderAuth;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\UnauthorizedHttpException;
use yiiunit\framework\filters\stubs\UserIdentity;

/**
 * @group filters
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.7
 */
class AuthTest extends \yiiunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $appConfig = [
            'components' => [
                'user' => [
                    'identityClass' => UserIdentity::class,
                ],
            ],
            'controllerMap' => [
                'test-auth' => TestAuthController::class,
            ],
        ];

        $this->mockWebApplication($appConfig);
    }

    public function tokenProvider()
    {
        return [
            ['token1', 'user1'],
            ['token2', 'user2'],
            ['token3', 'user3'],
            ['unknown', null],
            [null, null],
        ];
    }

    public function authOnly($token, $login, $filter)
    {
        /** @var TestAuthController $controller */
        $controller = Yii::$app->createController('test-auth')[0];
        $controller->authenticatorConfig = ArrayHelper::merge($filter, ['only' => ['filtered']]);
        try {
            $this->assertEquals($login, $controller->run('filtered'));
        } catch (UnauthorizedHttpException $e) {
        }
    }

    public function authOptional($token, $login, $filter)
    {
        /** @var TestAuthController $controller */
        $controller = Yii::$app->createController('test-auth')[0];
        $controller->authenticatorConfig = ArrayHelper::merge($filter, ['optional' => ['filtered']]);
        try {
            $this->assertEquals($login, $controller->run('filtered'));
        } catch (UnauthorizedHttpException $e) {
        }
    }

    public function authExcept($token, $login, $filter)
    {
        /** @var TestAuthController $controller */
        $controller = Yii::$app->createController('test-auth')[0];
        $controller->authenticatorConfig = ArrayHelper::merge($filter, ['except' => ['other']]);
        try {
            $this->assertEquals($login, $controller->run('filtered'));
        } catch (UnauthorizedHttpException $e) {
        }
    }

    public function ensureFilterApplies($token, $login, $filter)
    {
        $this->authOnly($token, $login, $filter);
        $this->authOptional($token, $login, $filter);
        $this->authExcept($token, $login, $filter);
    }

    /**
     * @dataProvider tokenProvider
     * @param string|null $token
     * @param string|null $login
     */
    public function testQueryParamAuth($token, $login)
    {
        $_GET['access-token'] = $token;
        $filter = ['__class' => QueryParamAuth::class];
        $this->authOnly($token, $login, $filter, 'query-param-auth');
        $this->authOptional($token, $login, $filter, 'query-param-auth');
        $this->authExcept($token, $login, $filter, 'query-param-auth');
    }

    /**
     * @dataProvider tokenProvider
     * @param string|null $token
     * @param string|null $login
     */
    public function testHttpBasicAuth($token, $login)
    {
        $_SERVER['PHP_AUTH_USER'] = $token;
        $_SERVER['PHP_AUTH_PW'] = 'whatever, we are testers';
        $filter = ['__class' => HttpBasicAuth::class];
        $this->authOnly($token, $login, $filter, 'basic-auth');
        $this->authOptional($token, $login, $filter, 'basic-auth');
        $this->authExcept($token, $login, $filter, 'basic-auth');
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
            '__class' => HttpBasicAuth::class,
            'auth' => function ($username, $password) {
                if (preg_match('/\d$/', $username)) {
                    return UserIdentity::findIdentity($username);
                }

                return null;
            },
        ];
        $this->authOnly($token, $login, $filter, 'basic-auth');
        $this->authOptional($token, $login, $filter, 'basic-auth');
        $this->authExcept($token, $login, $filter, 'basic-auth');
    }

    /**
     * @dataProvider tokenProvider
     * @param string|null $token
     * @param string|null $login
     */
    public function testHttpHeaderAuth($token, $login)
    {
        Yii::$app->request->setHeader('X-Api-Key', $token);
        $filter = ['__class' => HttpHeaderAuth::class];
        $this->ensureFilterApplies($token, $login, $filter);
    }

    /**
     * @dataProvider tokenProvider
     * @param string|null $token
     * @param string|null $login
     */
    public function testHttpBearerAuth($token, $login)
    {
        Yii::$app->request->addHeader('Authorization', "Bearer $token");
        $filter = ['__class' => HttpBearerAuth::class];
        $this->authOnly($token, $login, $filter, 'bearer-auth');
        $this->authOptional($token, $login, $filter, 'bearer-auth');
        $this->authExcept($token, $login, $filter, 'bearer-auth');
    }

    public function authMethodProvider()
    {
        return [
            [\yii\filters\auth\CompositeAuth::class],
            [\yii\filters\auth\HttpBearerAuth::class],
            [\yii\filters\auth\QueryParamAuth::class],
            [\yii\filters\auth\HttpHeaderAuth::class],
        ];
    }

    /**
     * @dataProvider authMethodProvider
     * @param string $authClass
     */
    public function testActive($authClass)
    {
        /** @var $filter AuthMethod */
        $filter = new $authClass();
        $reflection = new \ReflectionClass($filter);
        $method = $reflection->getMethod('isActive');
        $method->setAccessible(true);

        $controller = new \yii\web\Controller('test', Yii::$app);

        // active by default
        $this->assertTrue($method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertTrue($method->invokeArgs($filter, [new Action('view', $controller)]));

        $filter->only = ['index'];
        $filter->except = [];
        $filter->optional = [];
        $this->assertTrue($method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertFalse($method->invokeArgs($filter, [new Action('view', $controller)]));

        $filter->only = ['index'];
        $filter->except = [];
        $filter->optional = ['view'];
        $this->assertTrue($method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertFalse($method->invokeArgs($filter, [new Action('view', $controller)]));

        $filter->only = ['index', 'view'];
        $filter->except = ['view'];
        $filter->optional = [];
        $this->assertTrue($method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertFalse($method->invokeArgs($filter, [new Action('view', $controller)]));

        $filter->only = ['index', 'view'];
        $filter->except = ['view'];
        $filter->optional = ['view'];
        $this->assertTrue($method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertFalse($method->invokeArgs($filter, [new Action('view', $controller)]));

        $filter->only = [];
        $filter->except = ['view'];
        $filter->optional = ['view'];
        $this->assertTrue($method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertFalse($method->invokeArgs($filter, [new Action('view', $controller)]));
    }

    public function testHeaders()
    {
        Yii::$app->request->setHeader('Authorization', "Bearer wrong_token");
        $filter = ['__class' => HttpBearerAuth::class];
        $controller = Yii::$app->createController('test-auth')[0];
        $controller->authenticatorConfig = ArrayHelper::merge($filter, ['only' => ['filtered']]);
        try {
            $controller->run('filtered');
            $this->fail('Should throw UnauthorizedHttpException');
        } catch (UnauthorizedHttpException $e) {
            $this->assertTrue(Yii::$app->getResponse()->hasHeader('www-authenticate'));
        }
    }
}

/**
 * Class TestAuthController.
 *
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.7
 */
class TestAuthController extends Controller
{
    public $authenticatorConfig = [];

    public function behaviors()
    {
        return ['authenticator' => $this->authenticatorConfig];
    }

    public function actionFiltered()
    {
        return Yii::$app->user->id;
    }
}
