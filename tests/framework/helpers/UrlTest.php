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
use yii\web\UrlManager;
use yii\widgets\Menu;
use yiiunit\framework\filters\stubs\UserIdentity;
use yiiunit\TestCase;

/**
 * UrlTest
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
                    'class' => 'yii\web\Request',
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

    protected function tearDown()
    {
        Yii::$app->getSession()->removeAll();
        parent::tearDown();
    }

    /**
     * Mocks controller action with parameters
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
        $this->assertSame('/base/index.php?r=page%2Fview', Url::toRoute(''));
        // a slash will be an absolute route representing the default route
        $this->assertSame('/base/index.php?r=', Url::toRoute('/'));
        $this->assertSame('http://example.com/base/index.php?r=page%2Fview', Url::toRoute('', true));
        $this->assertSame('https://example.com/base/index.php?r=page%2Fview', Url::toRoute('', 'https'));
        $this->assertSame('//example.com/base/index.php?r=page%2Fview', Url::toRoute('', ''));

        // If the route contains no slashes at all, it is considered to be an action ID of the current controller and
        // will be prepended with uniqueId;
        $this->assertSame('/base/index.php?r=page%2Fedit', Url::toRoute('edit'));
        $this->assertSame('/base/index.php?r=page%2Fedit&id=20', Url::toRoute(['edit', 'id' => 20]));
        $this->assertSame('http://example.com/base/index.php?r=page%2Fedit&id=20', Url::toRoute(['edit', 'id' => 20], true));
        $this->assertSame('https://example.com/base/index.php?r=page%2Fedit&id=20', Url::toRoute(['edit', 'id' => 20], 'https'));
        $this->assertSame('//example.com/base/index.php?r=page%2Fedit&id=20', Url::toRoute(['edit', 'id' => 20], ''));

        // If the route has no leading slash, it is considered to be a route relative
        // to the current module and will be prepended with the module's uniqueId.
        $this->mockAction('default', 'index', 'stats');
        $this->assertSame('/base/index.php?r=stats%2Fuser%2Fview', Url::toRoute('user/view'));
        $this->assertSame('/base/index.php?r=stats%2Fuser%2Fview&id=42', Url::toRoute(['user/view', 'id' => 42]));
        $this->assertSame('http://example.com/base/index.php?r=stats%2Fuser%2Fview&id=42', Url::toRoute(['user/view', 'id' => 42], true));
        $this->assertSame('https://example.com/base/index.php?r=stats%2Fuser%2Fview&id=42', Url::toRoute(['user/view', 'id' => 42], 'https'));
        $this->assertSame('//example.com/base/index.php?r=stats%2Fuser%2Fview&id=42', Url::toRoute(['user/view', 'id' => 42], ''));

        // alias support
        \Yii::setAlias('@userView', 'user/view');
        $this->assertSame('/base/index.php?r=stats%2Fuser%2Fview', Url::toRoute('@userView'));
        \Yii::setAlias('@userView', null);

        // In case there is no controller, an exception should be thrown for relative route
        $this->removeMockedAction();

        $this->expectException('yii\base\InvalidParamException');
        Url::toRoute('site/view');
    }

    public function testCurrent()
    {
        $this->mockAction('page', 'view', null, []);
        \Yii::$app->request->setQueryParams(['id' => 10, 'name' => 'test']);

        $this->assertSame('/base/index.php?r=page%2Fview&id=10&name=test', Url::current());

        $this->assertSame('/base/index.php?r=page%2Fview&id=20&name=test', Url::current(['id' => 20]));

        $this->assertSame('/base/index.php?r=page%2Fview&name=test', Url::current(['id' => null]));
    }

    public function testPrevious()
    {
        Yii::$app->getUser()->login(UserIdentity::findIdentity('user1'));

        $this->assertNull(Url::previous('notExistedPage'));

        $this->assertNull(Url::previous(Yii::$app->getUser()->returnUrlParam));

        $this->assertSame('/base/index.php', Url::previous());
    }

    public function testTo()
    {
        // is an array: the first array element is considered a route, while the rest of the name-value
        // pairs are treated as the parameters to be used for URL creation using Url::toRoute.
        $this->mockAction('page', 'view', null, ['id' => 10]);
        $this->assertSame('/base/index.php?r=page%2Fedit&id=20', Url::to(['edit', 'id' => 20]));
        $this->assertSame('/base/index.php?r=page%2Fedit', Url::to(['edit']));
        $this->assertSame('/base/index.php?r=page%2Fview', Url::to(['']));

        // alias support
        \Yii::setAlias('@pageEdit', 'edit');
        $this->assertSame('/base/index.php?r=page%2Fedit&id=20', Url::to(['@pageEdit', 'id' => 20]));
        \Yii::setAlias('@pageEdit', null);

        $this->assertSame('http://example.com/base/index.php?r=page%2Fedit&id=20', Url::to(['edit', 'id' => 20], true));
        $this->assertSame('http://example.com/base/index.php?r=page%2Fedit', Url::to(['edit'], true));
        $this->assertSame('http://example.com/base/index.php?r=page%2Fview', Url::to([''], true));

        $this->assertSame('https://example.com/base/index.php?r=page%2Fedit&id=20', Url::to(['edit', 'id' => 20], 'https'));
        $this->assertSame('https://example.com/base/index.php?r=page%2Fedit', Url::to(['edit'], 'https'));
        $this->assertSame('https://example.com/base/index.php?r=page%2Fview', Url::to([''], 'https'));

        // is an empty string: the currently requested URL will be returned;
        $this->mockAction('page', 'view', null, ['id' => 10]);
        $this->assertSame('/base/index.php&r=site%2Fcurrent&id=42', Url::to(''));
        $this->assertSame('http://example.com/base/index.php&r=site%2Fcurrent&id=42', Url::to('', true));
        $this->assertSame('https://example.com/base/index.php&r=site%2Fcurrent&id=42', Url::to('', 'https'));

        // is a non-empty string: it will first be processed by [[Yii::getAlias()]]. If the result
        // is an absolute URL, it will be returned either without any change or, if schema was specified, with schema
        // replaced; Otherwise, the result will be prefixed with [[\yii\web\Request::baseUrl]] and returned.
        \Yii::setAlias('@web1', 'http://test.example.com/test/me1');
        \Yii::setAlias('@web2', 'test/me2');
        \Yii::setAlias('@web3', '');
        \Yii::setAlias('@web4', '/test');
        \Yii::setAlias('@web5', '#test');

        $this->assertSame('test/me1', Url::to('test/me1'));
        $this->assertSame('javascript:test/me1', Url::to('javascript:test/me1'));
        $this->assertSame('java/script:test/me1', Url::to('java/script:test/me1'));
        $this->assertSame('#test/me1', Url::to('#test/me1'));
        $this->assertSame('.test/me1', Url::to('.test/me1'));
        $this->assertSame('http://example.com/test/me1', Url::to('test/me1', true));
        $this->assertSame('https://example.com/test/me1', Url::to('test/me1', 'https'));
        $this->assertSame('https://example.com/test/test/me1', Url::to('@web4/test/me1', 'https'));

        $this->assertSame('/test/me1', Url::to('/test/me1'));
        $this->assertSame('http://example.com/test/me1', Url::to('/test/me1', true));
        $this->assertSame('https://example.com/test/me1', Url::to('/test/me1', 'https'));
        $this->assertSame('./test/me1', Url::to('./test/me1'));

        $this->assertSame('http://test.example.com/test/me1', Url::to('@web1'));
        $this->assertSame('http://test.example.com/test/me1', Url::to('@web1', true));
        $this->assertSame('https://test.example.com/test/me1', Url::to('@web1', 'https'));

        $this->assertSame('test/me2', Url::to('@web2'));
        $this->assertSame('http://example.com/test/me2', Url::to('@web2', true));
        $this->assertSame('https://example.com/test/me2', Url::to('@web2', 'https'));

        $this->assertSame('/base/index.php&r=site%2Fcurrent&id=42', Url::to('@web3'));
        $this->assertSame('http://example.com/base/index.php&r=site%2Fcurrent&id=42', Url::to('@web3', true));
        $this->assertSame('https://example.com/base/index.php&r=site%2Fcurrent&id=42', Url::to('@web3', 'https'));

        $this->assertSame('/test', Url::to('@web4'));
        $this->assertSame('http://example.com/test', Url::to('@web4', true));
        $this->assertSame('https://example.com/test', Url::to('@web4', 'https'));

        $this->assertSame('#test', Url::to('@web5'));
        $this->assertSame('http://example.com/#test', Url::to('@web5', true));
        $this->assertSame('https://example.com/#test', Url::to('@web5', 'https'));
        $this->assertSame('//example.com/#test', Url::to('@web5', ''));

        // @see https://github.com/yiisoft/yii2/issues/13156
        \Yii::setAlias('@cdn', '//cdn.example.com');
        $this->assertSame('http://cdn.example.com/images/logo.gif', Url::to('@cdn/images/logo.gif', 'http'));
        $this->assertSame('//cdn.example.com/images/logo.gif', Url::to('@cdn/images/logo.gif', ''));
        $this->assertSame('https://cdn.example.com/images/logo.gif', Url::to('@cdn/images/logo.gif', 'https'));
        \Yii::setAlias('@cdn', null);

        //In case there is no controller, throw an exception
        $this->removeMockedAction();

        $this->expectException('yii\base\InvalidParamException');
        Url::to(['site/view']);
    }

    /**
     * https://github.com/yiisoft/yii2/issues/11925
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
        $this->assertSame('/site/page.html?view=about', $url);

        $url = Url::to(['/site/page', 'view' => 'about']);
        $this->assertSame('/site/page.html?view=about', $url);

        $output = Menu::widget([
            'items' => [
                ['label' => 'Test', 'url' => ['/site/page', 'view' => 'about']],
            ],
        ]);
        $this->assertRegExp('~<a href="/site/page.html\?view=about">~', $output);
    }

    public function testBase()
    {
        $this->mockAction('page', 'view', null, ['id' => 10]);
        $this->assertSame('/base', Url::base());
        $this->assertSame('http://example.com/base', Url::base(true));
        $this->assertSame('https://example.com/base', Url::base('https'));
        $this->assertSame('//example.com/base', Url::base(''));
    }

    public function testHome()
    {
        $this->assertSame('/base/index.php', Url::home());
        $this->assertSame('http://example.com/base/index.php', Url::home(true));
        $this->assertSame('https://example.com/base/index.php', Url::home('https'));
        $this->assertSame('//example.com/base/index.php', Url::home(''));
    }

    public function testCanonical()
    {
        $this->mockAction('page', 'view', null, ['id' => 10]);
        $this->assertSame('http://example.com/base/index.php?r=page%2Fview&id=10', Url::canonical());
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
