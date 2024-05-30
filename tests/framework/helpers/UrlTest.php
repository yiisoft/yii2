<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use Yii;
use yii\base\Action;
use yii\base\Module;
use yii\helpers\Url;
use yii\web\Controller;
use yii\widgets\Menu;
use yiiunit\framework\filters\stubs\UserIdentity;
use yiiunit\TestCase;

/**
 * UrlTest.
 * @group helpers
 */
class UrlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication([
            'components' => [
                'request' => [
                    'class' => 'yii\web\Request',
                    'cookieValidationKey' => '123',
                    'scriptUrl' => '/base/index.php',
                    'hostInfo' => 'http://example.com/',
                    'url' => '/base/index.php&r=site%2Fcurrent&id=42',
                ],
                'urlManager' => [
                    'class' => 'yii\web\UrlManager',
                    'baseUrl' => '/base',
                    'scriptUrl' => '/base/index.php',
                    'hostInfo' => 'http://example.com/',
                ],
                'user' => [
                    'identityClass' => UserIdentity::className(),
                ],
            ],
        ], '\yii\web\Application');
    }

    protected function tearDown(): void
    {
        Yii::$app->getSession()->removeAll();
        parent::tearDown();
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

    public function testToRoute()
    {
        $this->mockAction('page', 'view', null, ['id' => 10]);

        // If the route is an empty string, the current route will be used;
        $this->assertEquals('/base/index.php?r=page%2Fview', Url::toRoute(''));
        // a slash will be an absolute route representing the default route
        $this->assertEquals('/base/index.php?r=', Url::toRoute('/'));
        $this->assertEquals('http://example.com/base/index.php?r=page%2Fview', Url::toRoute('', true));
        $this->assertEquals('https://example.com/base/index.php?r=page%2Fview', Url::toRoute('', 'https'));
        $this->assertEquals('//example.com/base/index.php?r=page%2Fview', Url::toRoute('', ''));

        // If the route contains no slashes at all, it is considered to be an action ID of the current controller and
        // will be prepended with uniqueId;
        $this->assertEquals('/base/index.php?r=page%2Fedit', Url::toRoute('edit'));
        $this->assertEquals('/base/index.php?r=page%2Fedit&id=20', Url::toRoute(['edit', 'id' => 20]));
        $this->assertEquals('http://example.com/base/index.php?r=page%2Fedit&id=20', Url::toRoute(['edit', 'id' => 20], true));
        $this->assertEquals('https://example.com/base/index.php?r=page%2Fedit&id=20', Url::toRoute(['edit', 'id' => 20], 'https'));
        $this->assertEquals('//example.com/base/index.php?r=page%2Fedit&id=20', Url::toRoute(['edit', 'id' => 20], ''));

        // If the route has no leading slash, it is considered to be a route relative
        // to the current module and will be prepended with the module's uniqueId.
        $this->mockAction('default', 'index', 'stats');
        $this->assertEquals('/base/index.php?r=stats%2Fuser%2Fview', Url::toRoute('user/view'));
        $this->assertEquals('/base/index.php?r=stats%2Fuser%2Fview&id=42', Url::toRoute(['user/view', 'id' => 42]));
        $this->assertEquals('http://example.com/base/index.php?r=stats%2Fuser%2Fview&id=42', Url::toRoute(['user/view', 'id' => 42], true));
        $this->assertEquals('https://example.com/base/index.php?r=stats%2Fuser%2Fview&id=42', Url::toRoute(['user/view', 'id' => 42], 'https'));
        $this->assertEquals('//example.com/base/index.php?r=stats%2Fuser%2Fview&id=42', Url::toRoute(['user/view', 'id' => 42], ''));

        // alias support
        \Yii::setAlias('@userView', 'user/view');
        $this->assertEquals('/base/index.php?r=stats%2Fuser%2Fview', Url::toRoute('@userView'));
        \Yii::setAlias('@userView', null);

        // In case there is no controller, an exception should be thrown for relative route
        $this->removeMockedAction();

        $this->expectException('yii\base\InvalidParamException');
        Url::toRoute('site/view');
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
    }

    public function testTo()
    {
        // is an array: the first array element is considered a route, while the rest of the name-value
        // pairs are treated as the parameters to be used for URL creation using Url::toRoute.
        $this->mockAction('page', 'view', null, ['id' => 10]);
        $this->assertEquals('/base/index.php?r=page%2Fedit&id=20', Url::to(['edit', 'id' => 20]));
        $this->assertEquals('/base/index.php?r=page%2Fedit', Url::to(['edit']));
        $this->assertEquals('/base/index.php?r=page%2Fview', Url::to(['']));

        // alias support
        \Yii::setAlias('@pageEdit', 'edit');
        $this->assertEquals('/base/index.php?r=page%2Fedit&id=20', Url::to(['@pageEdit', 'id' => 20]));
        \Yii::setAlias('@pageEdit', null);

        $this->assertEquals('http://example.com/base/index.php?r=page%2Fedit&id=20', Url::to(['edit', 'id' => 20], true));
        $this->assertEquals('http://example.com/base/index.php?r=page%2Fedit', Url::to(['edit'], true));
        $this->assertEquals('http://example.com/base/index.php?r=page%2Fview', Url::to([''], true));

        $this->assertEquals('https://example.com/base/index.php?r=page%2Fedit&id=20', Url::to(['edit', 'id' => 20], 'https'));
        $this->assertEquals('https://example.com/base/index.php?r=page%2Fedit', Url::to(['edit'], 'https'));
        $this->assertEquals('https://example.com/base/index.php?r=page%2Fview', Url::to([''], 'https'));

        // is an empty string: the currently requested URL will be returned;
        $this->mockAction('page', 'view', null, ['id' => 10]);
        $this->assertEquals('/base/index.php&r=site%2Fcurrent&id=42', Url::to(''));
        $this->assertEquals('http://example.com/base/index.php&r=site%2Fcurrent&id=42', Url::to('', true));
        $this->assertEquals('https://example.com/base/index.php&r=site%2Fcurrent&id=42', Url::to('', 'https'));

        // is a non-empty string: it will first be processed by [[Yii::getAlias()]]. If the result
        // is an absolute URL, it will be returned either without any change or, if schema was specified, with schema
        // replaced; Otherwise, the result will be prefixed with [[\yii\web\Request::baseUrl]] and returned.
        \Yii::setAlias('@web1', 'http://test.example.com/test/me1');
        \Yii::setAlias('@web2', 'test/me2');
        \Yii::setAlias('@web3', '');
        \Yii::setAlias('@web4', '/test');
        \Yii::setAlias('@web5', '#test');

        $this->assertEquals('test/me1', Url::to('test/me1'));
        $this->assertEquals('javascript:test/me1', Url::to('javascript:test/me1'));
        $this->assertEquals('java/script:test/me1', Url::to('java/script:test/me1'));
        $this->assertEquals('#test/me1', Url::to('#test/me1'));
        $this->assertEquals('.test/me1', Url::to('.test/me1'));
        $this->assertEquals('http://example.com/test/me1', Url::to('test/me1', true));
        $this->assertEquals('https://example.com/test/me1', Url::to('test/me1', 'https'));
        $this->assertEquals('https://example.com/test/test/me1', Url::to('@web4/test/me1', 'https'));

        $this->assertEquals('/test/me1', Url::to('/test/me1'));
        $this->assertEquals('http://example.com/test/me1', Url::to('/test/me1', true));
        $this->assertEquals('https://example.com/test/me1', Url::to('/test/me1', 'https'));
        $this->assertEquals('./test/me1', Url::to('./test/me1'));

        $this->assertEquals('http://test.example.com/test/me1', Url::to('@web1'));
        $this->assertEquals('http://test.example.com/test/me1', Url::to('@web1', true));
        $this->assertEquals('https://test.example.com/test/me1', Url::to('@web1', 'https'));

        $this->assertEquals('test/me2', Url::to('@web2'));
        $this->assertEquals('http://example.com/test/me2', Url::to('@web2', true));
        $this->assertEquals('https://example.com/test/me2', Url::to('@web2', 'https'));

        $this->assertEquals('/base/index.php&r=site%2Fcurrent&id=42', Url::to('@web3'));
        $this->assertEquals('http://example.com/base/index.php&r=site%2Fcurrent&id=42', Url::to('@web3', true));
        $this->assertEquals('https://example.com/base/index.php&r=site%2Fcurrent&id=42', Url::to('@web3', 'https'));

        $this->assertEquals('/test', Url::to('@web4'));
        $this->assertEquals('http://example.com/test', Url::to('@web4', true));
        $this->assertEquals('https://example.com/test', Url::to('@web4', 'https'));

        $this->assertEquals('#test', Url::to('@web5'));
        $this->assertEquals('http://example.com/#test', Url::to('@web5', true));
        $this->assertEquals('https://example.com/#test', Url::to('@web5', 'https'));
        $this->assertEquals('//example.com/#test', Url::to('@web5', ''));

        // @see https://github.com/yiisoft/yii2/issues/13156
        \Yii::setAlias('@cdn', '//cdn.example.com');
        $this->assertEquals('http://cdn.example.com/images/logo.gif', Url::to('@cdn/images/logo.gif', 'http'));
        $this->assertEquals('//cdn.example.com/images/logo.gif', Url::to('@cdn/images/logo.gif', ''));
        $this->assertEquals('https://cdn.example.com/images/logo.gif', Url::to('@cdn/images/logo.gif', 'https'));
        \Yii::setAlias('@cdn', null);

        //In case there is no controller, throw an exception
        $this->removeMockedAction();

        $this->expectException('yii\base\InvalidParamException');
        Url::to(['site/view']);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/11925
     */
    public function testToWithSuffix()
    {
        Yii::$app->set('urlManager', [
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'cache' => null,
            'rules' => [
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
            'baseUrl' => '/',
            'scriptUrl' => '/index.php',
            'suffix' => '.html',
        ]);
        $url = Yii::$app->urlManager->createUrl(['/site/page', 'view' => 'about']);
        $this->assertEquals('/site/page.html?view=about', $url);

        $url = Url::to(['/site/page', 'view' => 'about']);
        $this->assertEquals('/site/page.html?view=about', $url);

        $output = Menu::widget([
            'items' => [
                ['label' => 'Test', 'url' => ['/site/page', 'view' => 'about']],
            ],
        ]);
        $this->assertMatchesRegularExpression('~<a href="/site/page.html\?view=about">~', $output);
    }

    public function testBase()
    {
        $this->mockAction('page', 'view', null, ['id' => 10]);
        $this->assertEquals('/base', Url::base());
        $this->assertEquals('http://example.com/base', Url::base(true));
        $this->assertEquals('https://example.com/base', Url::base('https'));
        $this->assertEquals('//example.com/base', Url::base(''));
    }

    public function testHome()
    {
        $this->assertEquals('/base/index.php', Url::home());
        $this->assertEquals('http://example.com/base/index.php', Url::home(true));
        $this->assertEquals('https://example.com/base/index.php', Url::home('https'));
        $this->assertEquals('//example.com/base/index.php', Url::home(''));
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

    public function testRemember()
    {
        Yii::$app->getUser()->login(UserIdentity::findIdentity('user1'));

        Url::remember('test');
        $this->assertSame('test', Yii::$app->getUser()->getReturnUrl());
        $this->assertSame('test', Yii::$app->getSession()->get(Yii::$app->getUser()->returnUrlParam));

        Yii::$app->getUser()->setReturnUrl(null);
        Url::remember('test', 'remember-test');
        $this->assertSame('test', Yii::$app->getSession()->get('remember-test'));
    }
}
