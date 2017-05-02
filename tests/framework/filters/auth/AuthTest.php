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
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
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
                    'identityClass' => UserIdentity::className(),
                ],
            ],
            'controllerMap' => [
                'test-auth' => TestAuthController::className(),
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

    public function authOnly($token, $login, $filter, $action)
    {
        /** @var TestAuthController $controller */
        $controller = Yii::$app->createController('test-auth')[0];
        $controller->authenticatorConfig = ArrayHelper::merge($filter, ['only' => [$action]]);
        try {
            $this->assertEquals($login, $controller->run($action));
        } catch (UnauthorizedHttpException $e) {
        }
    }

    public function authOptional($token, $login, $filter, $action)
    {
        /** @var TestAuthController $controller */
        $controller = Yii::$app->createController('test-auth')[0];
        $controller->authenticatorConfig = ArrayHelper::merge($filter, ['optional' => [$action]]);
        try {
            $this->assertEquals($login, $controller->run($action));
        } catch (UnauthorizedHttpException $e) {
        }
    }

    public function authExcept($token, $login, $filter, $action)
    {
        /** @var TestAuthController $controller */
        $controller = Yii::$app->createController('test-auth')[0];
        $controller->authenticatorConfig = ArrayHelper::merge($filter, ['except' => ['other']]);
        try {
            $this->assertEquals($login, $controller->run($action));
        } catch (UnauthorizedHttpException $e) {
        }
    }

    /**
     * @dataProvider tokenProvider
     */
    public function testQueryParamAuth($token, $login)
    {
        $_GET['access-token'] = $token;
        $filter = ['class' => QueryParamAuth::className()];
        $this->authOnly($token, $login, $filter, 'query-param-auth');
        $this->authOptional($token, $login, $filter, 'query-param-auth');
        $this->authExcept($token, $login, $filter, 'query-param-auth');
    }

    /**
     * @dataProvider tokenProvider
     */
    public function testHttpBasicAuth($token, $login)
    {
        $_SERVER['PHP_AUTH_USER'] = $token;
        $_SERVER['PHP_AUTH_PW'] = 'whatever, we are testers';
        $filter = ['class' => HttpBasicAuth::className()];
        $this->authOnly($token, $login, $filter, 'basic-auth');
        $this->authOptional($token, $login, $filter, 'basic-auth');
        $this->authExcept($token, $login, $filter, 'basic-auth');
    }

    /**
     * @dataProvider tokenProvider
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
        $this->authOnly($token, $login, $filter, 'basic-auth');
        $this->authOptional($token, $login, $filter, 'basic-auth');
        $this->authExcept($token, $login, $filter, 'basic-auth');
    }

    /**
     * @dataProvider tokenProvider
     */
    public function testHttpBearerAuth($token, $login)
    {
        Yii::$app->request->headers->set('Authorization', "Bearer $token");
        $filter = ['class' => HttpBearerAuth::className()];
        $this->authOnly($token, $login, $filter, 'bearer-auth');
        $this->authOptional($token, $login, $filter, 'bearer-auth');
        $this->authExcept($token, $login, $filter, 'bearer-auth');
    }

    public function authMethodProvider()
    {
        return [
            ['yii\filters\auth\CompositeAuth'],
            ['yii\filters\auth\HttpBasicAuth'],
            ['yii\filters\auth\HttpBearerAuth'],
            ['yii\filters\auth\QueryParamAuth'],
        ];
    }

    /**
     * @dataProvider authMethodProvider
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
        $this->assertEquals(true, $method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertEquals(true, $method->invokeArgs($filter, [new Action('view', $controller)]));

        $filter->only = ['index'];
        $filter->except = [];
        $filter->optional = [];
        $this->assertEquals(true, $method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertEquals(false, $method->invokeArgs($filter, [new Action('view', $controller)]));

        $filter->only = ['index'];
        $filter->except = [];
        $filter->optional = ['view'];
        $this->assertEquals(true, $method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertEquals(false, $method->invokeArgs($filter, [new Action('view', $controller)]));

        $filter->only = ['index', 'view'];
        $filter->except = ['view'];
        $filter->optional = [];
        $this->assertEquals(true, $method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertEquals(false, $method->invokeArgs($filter, [new Action('view', $controller)]));

        $filter->only = ['index', 'view'];
        $filter->except = ['view'];
        $filter->optional = ['view'];
        $this->assertEquals(true, $method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertEquals(false, $method->invokeArgs($filter, [new Action('view', $controller)]));

        $filter->only = [];
        $filter->except = ['view'];
        $filter->optional = ['view'];
        $this->assertEquals(true, $method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertEquals(false, $method->invokeArgs($filter, [new Action('view', $controller)]));
    }
}

/**
 * Class TestAuthController
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

    public function actionBasicAuth()
    {
        return Yii::$app->user->id;
    }

    public function actionBearerAuth()
    {
        return Yii::$app->user->id;
    }

    public function actionQueryParamAuth()
    {
        return Yii::$app->user->id;
    }
}
