<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\filters\auth;

use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\HttpHeaderAuth;
use yii\rest\Controller;
use yiiunit\framework\web\UserIdentity;

/**
 * @author Ezekiel Fernandez <ezekiel_p_fernandez@yahoo.com>
 */
class TestAuth extends HttpHeaderAuth
{
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

            $identity = \yiiunit\framework\filters\stubs\UserIdentity::findIdentity($authHeader);
            if ($identity === null) {
                $this->challenge($response);
                $this->handleFailure($response);
            }

            return $identity;
        }

        return null;
    }
}

class TestController extends Controller
{
    public $authMethods = [];

    public $optional = [];

    public function actionA()
    {
        return 'success';
    }

    public function actionB()
    {
        /*
         * this call will execute the actionA in a same instance of TestController
         */
        return $this->runAction('a');
    }

    public function actionC()
    {
        /*
         * this call will execute the actionA in a same instance of TestController
         */
        return $this->run('a');
    }

    public function actionD()
    {
        /*
         * this call will execute the actionA in a new instance of TestController
         */
        return $this->run('test/a');
    }

    public function behaviors()
    {
        /*
         * the CompositeAuth::authenticate() assumes that it is only executed once per the controller's instance
         * i believe this is okay as long as we specify in the documentation that if we want to use the authenticate
         * method again(this might even be also true to other behaviors that attaches to the beforeAction event),
         * that we will have to forward/run into the other action in a way that it will create a new controller instance
         */
        return [
            'authenticator' => [
                'class' => CompositeAuth::className(),
                'authMethods' => $this->authMethods ?: [TestAuth::className()],
                'optional' => $this->optional
            ],
        ];
    }
}

/**
 * @group filters
 */
class CompositeAuthTest extends \yiiunit\TestCase
{
    protected function setUp(): void
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
                'test' => TestController::className(),
            ],
        ];

        $this->mockWebApplication($appConfig);
    }

    public function testCallingRunWithCompleteRoute()
    {
        /** @var TestController $controller */
        Yii::$app->request->headers->set('X-Api-Key', 'user1');
        $controller = Yii::$app->createController('test')[0];
        $this->assertEquals('success', $controller->run('test/d'));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/7409
     */
    public function testRunAction()
    {
        /** @var TestController $controller */
        Yii::$app->request->headers->set('X-Api-Key', 'user1');
        $controller = Yii::$app->createController('test')[0];
        $this->assertEquals('success', $controller->run('b'));
    }

    public function testRunButWithActionIdOnly()
    {
        /** @var TestController $controller */
        Yii::$app->request->headers->set('X-Api-Key', 'user1');
        $controller = Yii::$app->createController('test')[0];
        $this->assertEquals('success', $controller->run('c'));
    }

    public function testRunWithWrongToken()
    {
        /** @var TestController $controller */
        Yii::$app->request->headers->set('X-Api-Key', 'wrong-user');
        $controller = Yii::$app->createController('test')[0];
        $this->expectException('yii\web\UnauthorizedHttpException');
        $controller->run('a');
    }

    public function testRunWithoutAuthHeader()
    {
        /** @var TestController $controller */
        $controller = Yii::$app->createController('test')[0];
        $this->expectException('yii\web\UnauthorizedHttpException');
        $controller->run('a');
    }

    public function testRunWithOptionalAction()
    {
        /** @var TestController $controller */
        $controller = Yii::$app->createController('test')[0];
        $controller->optional = ['a'];
        $this->assertEquals('success', $controller->run('a'));
    }

    public function compositeAuthDataProvider()
    {
        return [
            //base usage
            [
                [
                    HttpBearerAuth::className(),
                    TestAuth::className(),
                ],
                'b',
                true
            ],
            //empty auth methods
            [
                [],
                'b',
                true
            ],
            //only "a", run "b"
            [
                [
                    HttpBearerAuth::className(),
                    [
                        'class' => TestAuth::className(),
                        'only' => ['a']
                    ],
                ],
                'b',
                false
            ],
            //only "a", run "a"
            [
                [
                    HttpBearerAuth::className(),
                    [
                        'class' => TestAuth::className(),
                        'only' => ['a']
                    ],
                ],
                'a',
                true
            ],
            //except "b", run "a"
            [
                [
                    HttpBearerAuth::className(),
                    [
                        'class' => TestAuth::className(),
                        'except' => ['b']
                    ],
                ],
                'a',
                true
            ],
            //except "b", run "b"
            [
                [
                    HttpBearerAuth::className(),
                    [
                        'class' => TestAuth::className(),
                        'except' => ['b']
                    ],
                ],
                'b',
                false
            ]
        ];
    }

    /**
     * @param array $authMethods
     * @param string $actionId
     * @param bool $expectedAuth
     *
     * @dataProvider compositeAuthDataProvider
     */
    public function testCompositeAuth($authMethods, $actionId, $expectedAuth)
    {
        Yii::$app->request->headers->set('X-Api-Key', 'user1');
        $controller = new TestController('test', Yii::$app, ['authMethods' => $authMethods]);
        if ($expectedAuth) {
            $this->assertEquals('success', $controller->run($actionId));
        } else {
            $this->expectException('yii\web\UnauthorizedHttpException');
            $controller->run($actionId);
        }
    }
}
