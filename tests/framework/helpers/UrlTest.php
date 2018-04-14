<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use Yii;
use yii\base\Action;
use yii\base\Module;
use yii\helpers\Url;
use yii\web\Controller;
use yiiunit\framework\filters\stubs\UserIdentity;
use yiiunit\TestCase;

/**
 * UrlTest.
 * @group helpers
 */
class UrlTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication([
            'components' => [
                'request' => [
                    '__class' => \yii\web\Request::class,
                    'cookieValidationKey' => '123',
                    'scriptUrl' => '/base/index.php',
                    'hostInfo' => 'http://example.com/',
                    'url' => '/base/index.php&r=site%2Fcurrent&id=42',
                ],
                'urlManager' => [
                    '__class' => \yii\web\UrlManager::class,
                    'baseUrl' => '/base',
                    'scriptUrl' => '/base/index.php',
                    'hostInfo' => 'http://example.com/',
                ],
                'user' => [
                    'identityClass' => UserIdentity::class,
                ],
            ],
        ], \yii\web\Application::class);
    }

    /**
     * Mocks controller action with parameters.
     *
     * @param string $controllerId
     * @param string $actionID
     * @param string $moduleID
     * @param array  $params
     */
    protected function mockAction($controllerId, $actionID, $moduleID = null, $params = [])
    {
        \Yii::$app->controller = $controller = new Controller($controllerId, \Yii::$app);
        $controller->actionParams = $params;
        $controller->action = new Action($actionID, $controller);

        if ($moduleID !== null) {
            $controller->module = new Module($moduleID);
        }
    }

    protected function removeMockedAction()
    {
        \Yii::$app->controller = null;
    }

    public function testCurrent()
    {
        $this->mockAction('page', 'view', null, []);
        Yii::$app->request->setQueryParams(['id' => 10, 'name' => 'test', 10 => 0]);
        $uri = '/base/index.php?r=page%2Fview';

        $this->assertEquals($uri . '&id=10&name=test&10=0', Url::current());
        $this->assertEquals($uri . '&id=20&name=test&10=0', Url::current(['id' => 20]));
        $this->assertEquals($uri . '&name=test&10=0', Url::current(['id' => null]));
        $this->assertEquals($uri . '&name=test&10=0&1=yes', Url::current(['id' => [], 1 => 'yes']));
        $this->assertEquals($uri . '&name=test&10=0', Url::current(['id' => []]));
        $this->assertEquals($uri . '&name=test', Url::current(['id' => null, 10 => null]));
        $this->assertEquals($uri . '&name=test&1=yes', Url::current(['id' => null, 10 => null, 1 => 'yes']));

        $params = ['arr' => ['attr_one' => 1, 'attr_two' => 2]];
        Yii::$app->request->setQueryParams($params);

        $this->assertEquals($uri . '&arr%5Battr_one%5D=1&arr%5Battr_two%5D=2', Url::current());
        $this->assertEquals($uri, Url::current(['arr' => null]));
        $this->assertEquals($uri . '&arr%5Battr_two%5D=2', Url::current(['arr' => ['attr_one' => null]]));
        $this->assertEquals($uri . '&arr%5Battr_one%5D=1&arr%5Battr_two%5D=two', Url::current(['arr' => ['attr_two' => 'two']]));
    }

    public function testPrevious()
    {
        Yii::$app->getUser()->login(UserIdentity::findIdentity('user1'));

        $this->assertNull(Url::previous('notExistedPage'));

        $this->assertNull(Url::previous(Yii::$app->getUser()->returnUrlParam));

        $this->assertEquals('/base/index.php', Url::previous());

        Yii::$app->getSession()->removeAll();
    }

    public function testCanonical()
    {
        $this->mockAction('page', 'view', null, ['id' => 10]);
        $this->assertEquals('http://example.com/base/index.php?r=page%2Fview&id=10', Url::canonical());
        $this->removeMockedAction();
    }

    public function testIsRelative()
    {
        $this->assertTrue(Url::isRelative('/test/index.php'));
        $this->assertTrue(Url::isRelative('index.php'));
        $this->assertFalse(Url::isRelative('//example.com/'));
        $this->assertFalse(Url::isRelative('http://example.com/'));
        $this->assertFalse(Url::isRelative('https://example.com/'));
    }
}
