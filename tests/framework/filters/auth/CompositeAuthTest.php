<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\filters\auth;

use Yii;
use yii\filters\auth\AuthMethod;
use yii\filters\auth\CompositeAuth;
use yii\rest\Controller;
use yiiunit\framework\web\UserIdentity;

/**
 * @author Ezekiel Fernandez <ezekiel_p_fernandez@yahoo.com>
 */
class TestAuth extends AuthMethod
{
    public function authenticate($user, $request, $response)
    {
        return $user;
    }
}

class TestController extends Controller
{
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
                'authMethods' => [
                    TestAuth::className(),
                ],
            ],
        ];
    }
}

/**
 * @group filters
 */
class CompositeAuthTest extends \yiiunit\TestCase
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
                'test' => TestController::className(),
            ],
        ];

        $this->mockWebApplication($appConfig);
    }

    public function testCallingRunWithCompleteRoute()
    {
        /** @var TestController $controller */
        $controller = Yii::$app->createController('test')[0];
        $this->assertEquals('success', $controller->run('test/d'));
    }

    /**
     * reproducing the issue specified in https://github.com/yiisoft/yii2/issues/7409
     */
    public function testRunAction()
    {
        /** @var TestController $controller */
        $controller = Yii::$app->createController('test')[0];
        $this->assertEquals('success', $controller->run('b'));
    }

    public function testRunButWithActionIdOnly()
    {
        /** @var TestController $controller */
        $controller = Yii::$app->createController('test')[0];
        $this->assertEquals('success', $controller->run('c'));
    }
}
