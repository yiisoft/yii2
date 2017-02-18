<?php

namespace yiiunit\framework\web;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\UserException;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\Request;
use yiiunit\TestCase;

/**
 * @group web
 */
class ErrorActionTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->mockApplication([
            'components' => [
                'request' => [
                    'class' => Request::class,
                ],
            ],
        ]);
    }

    public function getController($actionConfig = [])
    {
        return new TestController('test', Yii::$app, ['layout' => false, 'actionConfig' => $actionConfig]);
    }

    public function testYiiException()
    {
        Yii::$app->getErrorHandler()->exception = new InvalidConfigException('This message will not be shown to the user');

        $this->assertEquals('Name: Invalid Configuration
Message: An internal server error occurred.
Exception: yii\base\InvalidConfigException', $this->getController()->runAction('error'));
    }

    public function testUserException()
    {
        Yii::$app->getErrorHandler()->exception = new UserException('User can see this error message');

        $this->assertEquals('Name: Exception
Message: User can see this error message
Exception: yii\base\UserException', $this->getController()->runAction('error'));
    }

    public function testAjaxRequest()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        $this->assertEquals('Not Found (#404): Page not found.', $this->getController()->runAction('error'));
    }

    public function testGenericException()
    {
        Yii::$app->getErrorHandler()->exception = new \InvalidArgumentException('This message will not be shown to the user');

        $this->assertEquals('Name: Error
Message: An internal server error occurred.
Exception: InvalidArgumentException', $this->getController()->runAction('error'));
    }

    public function testGenericExceptionCustomNameAndMessage()
    {
        Yii::$app->getErrorHandler()->exception = new \InvalidArgumentException('This message will not be shown to the user');

        $controller = $this->getController([
            'defaultName' => 'Oops...',
            'defaultMessage' => 'The system is drunk'
        ]);

        $this->assertEquals('Name: Oops...
Message: The system is drunk
Exception: InvalidArgumentException', $controller->runAction('error'));
    }

    public function testNoExceptionInHandler()
    {
        $this->assertEquals('Name: Not Found (#404)
Message: Page not found.
Exception: yii\web\NotFoundHttpException', $this->getController()->runAction('error'));
    }

    public function testDefaultView()
    {
        /** @var ErrorAction $action */
        $action = $this->getController()->createAction('error');

        // Unset view name. Class should try to load view that matches action name by default
        $action->view = null;
        $this->setExpectedExceptionRegExp('yii\base\ViewNotFoundException', '#The view file does not exist: .*?views/test/error.php#');
        $this->invokeMethod($action, 'renderHtmlResponse');
    }


}

class TestController extends Controller
{
    private $actionConfig;

    public function setActionConfig($config = [])
    {
        $this->actionConfig = $config;
    }

    public function actions()
    {
        return [
            'error' => array_merge([
                'class' => ErrorAction::class,
                'view' => '@yiiunit/data/views/error.php',
            ], $this->actionConfig),
        ];
    }
}
