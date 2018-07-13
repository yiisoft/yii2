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
use yii\web\Controller;
use yii\widgets\Menu;
use yiiunit\framework\filters\stubs\UserIdentity;
use yiiunit\TestCase;

/**
 * UrlTest.
 * @group helpers
 */
class UrlManagerBuildUrlTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication([
            'components' => [
                'request' => [
                    '__class' => \yii\web\Request::class,
                    'baseUrl' => '/base',
                    'scriptUrl' => '/base/index.php',
                    'hostInfo' => 'http://example.com/',
                    'url' => '/base/index.php&r=site%2Fcurrent&id=42',
                ],
                'urlManager' => [
                    '__class' => \yii\web\UrlManager::class,
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
        Yii::$app->controller = $controller = new Controller($controllerId, Yii::$app);
        $controller->actionParams = $params;
        $controller->action = new Action($actionID, $controller);

        if ($moduleID !== null) {
            $controller->module = new Module($moduleID);
        }
    }

    protected function removeMockedAction()
    {
        Yii::$app->controller = null;
    }

    public function testToRoute()
    {
        $manager = Yii::$app->getUrlManager();

        $this->mockAction('page', 'view', null, ['id' => 10]);

        // If the route is an empty string, the current route will be used;
        $this->assertEquals('/base/index.php?r=page%2Fview', $manager->createUrlToRoute(''));
        // a slash will be an absolute route representing the default route
        $this->assertEquals('/base/index.php?r=', $manager->createUrlToRoute('/'));
        $this->assertEquals('http://example.com/base/index.php?r=page%2Fview', $manager->createUrlToRoute('', true));
        $this->assertEquals('https://example.com/base/index.php?r=page%2Fview', $manager->createUrlToRoute('', 'https'));
        $this->assertEquals('//example.com/base/index.php?r=page%2Fview', $manager->createUrlToRoute('', ''));

        // If the route contains no slashes at all, it is considered to be an action ID of the current controller and
        // will be prepended with uniqueId;
        $this->assertEquals('/base/index.php?r=page%2Fedit', $manager->createUrlToRoute('edit'));
        $this->assertEquals('/base/index.php?r=page%2Fedit&id=20', $manager->createUrlToRoute(['edit', 'id' => 20]));
        $this->assertEquals('http://example.com/base/index.php?r=page%2Fedit&id=20', $manager->createUrlToRoute(['edit', 'id' => 20], true));
        $this->assertEquals('https://example.com/base/index.php?r=page%2Fedit&id=20', $manager->createUrlToRoute(['edit', 'id' => 20], 'https'));
        $this->assertEquals('//example.com/base/index.php?r=page%2Fedit&id=20', $manager->createUrlToRoute(['edit', 'id' => 20], ''));

        // If the route has no leading slash, it is considered to be a route relative
        // to the current module and will be prepended with the module's uniqueId.
        $this->mockAction('default', 'index', 'stats');
        $this->assertEquals('/base/index.php?r=stats%2Fuser%2Fview', $manager->createUrlToRoute('user/view'));
        $this->assertEquals('/base/index.php?r=stats%2Fuser%2Fview&id=42', $manager->createUrlToRoute(['user/view', 'id' => 42]));
        $this->assertEquals('http://example.com/base/index.php?r=stats%2Fuser%2Fview&id=42', $manager->createUrlToRoute(['user/view', 'id' => 42], true));
        $this->assertEquals('https://example.com/base/index.php?r=stats%2Fuser%2Fview&id=42', $manager->createUrlToRoute(['user/view', 'id' => 42], 'https'));
        $this->assertEquals('//example.com/base/index.php?r=stats%2Fuser%2Fview&id=42', $manager->createUrlToRoute(['user/view', 'id' => 42], ''));

        // alias support
        Yii::setAlias('@userView', 'user/view');
        $this->assertEquals('/base/index.php?r=stats%2Fuser%2Fview', $manager->createUrlToRoute('@userView'));
        Yii::setAlias('@userView', null);

        // In case there is no controller, an exception should be thrown for relative route
        $this->removeMockedAction();

        $this->expectException('yii\base\InvalidConfigException');
        $manager->createUrlToRoute('site/view');
    }

    public function testCreateUrlTo()
    {
        $manager = Yii::$app->getUrlManager();
        // is an array: the first array element is considered a route, while the rest of the name-value
        // pairs are treated as the parameters to be used for URL creation using Yii::$app->urlManager->toRoute().
        $this->mockAction('page', 'view', null, ['id' => 10]);
        $this->assertEquals('/base/index.php?r=page%2Fedit&id=20', $manager->createUrlTo(['edit', 'id' => 20]));
        $this->assertEquals('/base/index.php?r=page%2Fedit', $manager->createUrlTo(['edit']));
        $this->assertEquals('/base/index.php?r=page%2Fview', $manager->createUrlTo(['']));

        // alias support
        Yii::setAlias('@pageEdit', 'edit');
        $this->assertEquals('/base/index.php?r=page%2Fedit&id=20', $manager->createUrlTo(['@pageEdit', 'id' => 20]));
        Yii::setAlias('@pageEdit', null);

        $this->assertEquals('http://example.com/base/index.php?r=page%2Fedit&id=20', $manager->createUrlTo(['edit', 'id' => 20], true));
        $this->assertEquals('http://example.com/base/index.php?r=page%2Fedit', $manager->createUrlTo(['edit'], true));
        $this->assertEquals('http://example.com/base/index.php?r=page%2Fview', $manager->createUrlTo([''], true));

        $this->assertEquals('https://example.com/base/index.php?r=page%2Fedit&id=20', $manager->createUrlTo(['edit', 'id' => 20], 'https'));
        $this->assertEquals('https://example.com/base/index.php?r=page%2Fedit', $manager->createUrlTo(['edit'], 'https'));
        $this->assertEquals('https://example.com/base/index.php?r=page%2Fview', $manager->createUrlTo([''], 'https'));

        // is an empty string: the currently requested URL will be returned;
        $this->mockAction('page', 'view', null, ['id' => 10]);
        $this->assertEquals('/base/index.php&r=site%2Fcurrent&id=42', $manager->createUrlTo(''));
        $this->assertEquals('http://example.com/base/index.php&r=site%2Fcurrent&id=42', $manager->createUrlTo('', true));
        $this->assertEquals('https://example.com/base/index.php&r=site%2Fcurrent&id=42', $manager->createUrlTo('', 'https'));

        // is a non-empty string: it will first be processed by [[Yii::getAlias()]]. If the result
        // is an absolute URL, it will be returned either without any change or, if schema was specified, with schema
        // replaced; Otherwise, the result will be prefixed with [[\yii\web\Request::baseUrl]] and returned.
        Yii::setAlias('@web1', 'http://test.example.com/test/me1');
        Yii::setAlias('@web2', 'test/me2');
        Yii::setAlias('@web3', '');
        Yii::setAlias('@web4', '/test');
        Yii::setAlias('@web5', '#test');

        $this->assertEquals('test/me1', $manager->createUrlTo('test/me1'));
        $this->assertEquals('javascript:test/me1', $manager->createUrlTo('javascript:test/me1'));
        $this->assertEquals('java/script:test/me1', $manager->createUrlTo('java/script:test/me1'));
        $this->assertEquals('#test/me1', $manager->createUrlTo('#test/me1'));
        $this->assertEquals('.test/me1', $manager->createUrlTo('.test/me1'));
        $this->assertEquals('http://example.com/test/me1', $manager->createUrlTo('test/me1', true));
        $this->assertEquals('https://example.com/test/me1', $manager->createUrlTo('test/me1', 'https'));
        $this->assertEquals('https://example.com/test/test/me1', $manager->createUrlTo('@web4/test/me1', 'https'));

        $this->assertEquals('/test/me1', $manager->createUrlTo('/test/me1'));
        $this->assertEquals('http://example.com/test/me1', $manager->createUrlTo('/test/me1', true));
        $this->assertEquals('https://example.com/test/me1', $manager->createUrlTo('/test/me1', 'https'));
        $this->assertEquals('./test/me1', $manager->createUrlTo('./test/me1'));

        $this->assertEquals('http://test.example.com/test/me1', $manager->createUrlTo('@web1'));
        $this->assertEquals('http://test.example.com/test/me1', $manager->createUrlTo('@web1', true));
        $this->assertEquals('https://test.example.com/test/me1', $manager->createUrlTo('@web1', 'https'));

        $this->assertEquals('test/me2', $manager->createUrlTo('@web2'));
        $this->assertEquals('http://example.com/test/me2', $manager->createUrlTo('@web2', true));
        $this->assertEquals('https://example.com/test/me2', $manager->createUrlTo('@web2', 'https'));

        $this->assertEquals('/base/index.php&r=site%2Fcurrent&id=42', $manager->createUrlTo('@web3'));
        $this->assertEquals('http://example.com/base/index.php&r=site%2Fcurrent&id=42', $manager->createUrlTo('@web3', true));
        $this->assertEquals('https://example.com/base/index.php&r=site%2Fcurrent&id=42', $manager->createUrlTo('@web3', 'https'));

        $this->assertEquals('/test', $manager->createUrlTo('@web4'));
        $this->assertEquals('http://example.com/test', $manager->createUrlTo('@web4', true));
        $this->assertEquals('https://example.com/test', $manager->createUrlTo('@web4', 'https'));

        $this->assertEquals('#test', $manager->createUrlTo('@web5'));
        $this->assertEquals('http://example.com/#test', $manager->createUrlTo('@web5', true));
        $this->assertEquals('https://example.com/#test', $manager->createUrlTo('@web5', 'https'));
        $this->assertEquals('//example.com/#test', $manager->createUrlTo('@web5', ''));

        // @see https://github.com/yiisoft/yii2/issues/13156
        Yii::setAlias('@cdn', '//cdn.example.com');
        $this->assertEquals('http://cdn.example.com/images/logo.gif', $manager->createUrlTo('@cdn/images/logo.gif', 'http'));
        $this->assertEquals('//cdn.example.com/images/logo.gif', $manager->createUrlTo('@cdn/images/logo.gif', ''));
        $this->assertEquals('https://cdn.example.com/images/logo.gif', $manager->createUrlTo('@cdn/images/logo.gif', 'https'));
        Yii::setAlias('@cdn', null);

        //In case there is no controller, throw an exception
        $this->removeMockedAction();

        $this->expectException('yii\base\InvalidConfigException');
        $manager->createUrlTo(['site/view']);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/11925
     */
    public function testToWithSuffix()
    {
        Yii::$app->set('urlManager', [
            '__class' => \yii\web\UrlManager::class,
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

        $manager = Yii::$app->getUrlManager();

        $url = $manager->createUrl(['/site/page', 'view' => 'about']);
        $this->assertEquals('/site/page.html?view=about', $url);

        $url = $manager->createUrlTo(['/site/page', 'view' => 'about']);
        $this->assertEquals('/site/page.html?view=about', $url);

        $output = Menu::widget([
            'items' => [
                ['label' => 'Test', 'url' => ['/site/page', 'view' => 'about']],
            ],
        ]);
        $this->assertRegExp('~<a href="/site/page.html\?view=about">~', $output);
    }

    public function testBaseUrl()
    {
        $manager = Yii::$app->getUrlManager();
        $this->mockAction('page', 'view', null, ['id' => 10]);
        $this->assertEquals('/base', $manager->getBaseUrl());
        $this->assertEquals('http://example.com/base', $manager->getBaseUrl(true));
        $this->assertEquals('https://example.com/base', $manager->getBaseUrl('https'));
        $this->assertEquals('//example.com/base', $manager->getBaseUrl(''));
    }

    public function testHomeUrl()
    {
        $manager = Yii::$app->getUrlManager();
        $this->assertEquals('/base/index.php', $manager->getHomeUrl());
        $this->assertEquals('http://example.com/base/index.php', $manager->getHomeUrl(true));
        $this->assertEquals('https://example.com/base/index.php', $manager->getHomeUrl('https'));
        $this->assertEquals('//example.com/base/index.php', $manager->getHomeUrl(''));
    }
}
