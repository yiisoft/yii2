<?php
namespace yiiunit\framework\helpers;

use yii\base\Action;
use yii\base\Module;
use yii\helpers\Url;
use yii\web\Controller;
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
                    'url' => '/base/index.php&r=site%2Fcurrent&id=42'
                ],
                'urlManager' => [
                    'class' => 'yii\web\UrlManager',
                    'baseUrl' => '/base',
                    'scriptUrl' => '/base/index.php',
                    'hostInfo' => 'http://example.com/',
                ]
            ],
        ], '\yii\web\Application');
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
        $this->assertEquals('/base/index.php?r=page%2Fview', Url::toRoute(''));
        $this->assertEquals('http://example.com/base/index.php?r=page%2Fview', Url::toRoute('', true));
        $this->assertEquals('https://example.com/base/index.php?r=page%2Fview', Url::toRoute('', 'https'));

        // If the route contains no slashes at all, it is considered to be an action ID of the current controller and
        // will be prepended with uniqueId;
        $this->assertEquals('/base/index.php?r=page%2Fedit', Url::toRoute('edit'));
        $this->assertEquals('/base/index.php?r=page%2Fedit&id=20', Url::toRoute(['edit', 'id' => 20]));
        $this->assertEquals('http://example.com/base/index.php?r=page%2Fedit&id=20', Url::toRoute(['edit', 'id' => 20], true));
        $this->assertEquals('https://example.com/base/index.php?r=page%2Fedit&id=20', Url::toRoute(['edit', 'id' => 20], 'https'));

        // If the route has no leading slash, it is considered to be a route relative
        // to the current module and will be prepended with the module's uniqueId.
        $this->mockAction('default', 'index', 'stats');
        $this->assertEquals('/base/index.php?r=stats%2Fuser%2Fview', Url::toRoute('user/view'));
        $this->assertEquals('/base/index.php?r=stats%2Fuser%2Fview&id=42', Url::toRoute(['user/view', 'id' => 42]));
        $this->assertEquals('http://example.com/base/index.php?r=stats%2Fuser%2Fview&id=42', Url::toRoute(['user/view', 'id' => 42], true));
        $this->assertEquals('https://example.com/base/index.php?r=stats%2Fuser%2Fview&id=42', Url::toRoute(['user/view', 'id' => 42], 'https'));

        // alias support
        \Yii::setAlias('@userView', 'user/view');
        $this->assertEquals('/base/index.php?r=stats%2Fuser%2Fview', Url::toRoute('@userView'));
        \Yii::setAlias('@userView', null);

        // In case there is no controller, an exception should be thrown for relative route
        $this->removeMockedAction();

        $this->setExpectedException('yii\base\InvalidParamException');
        Url::toRoute('site/view');
    }

    public function testCurrent()
    {
        $this->mockAction('page', 'view', null, []);
        \Yii::$app->request->setQueryParams(['id' => 10, 'name' => 'test']);

        $this->assertEquals('/base/index.php?r=page%2Fview&id=10&name=test', Url::current());

        $this->assertEquals('/base/index.php?r=page%2Fview&id=20&name=test', Url::current(['id' => 20]));

        $this->assertEquals('/base/index.php?r=page%2Fview&name=test', Url::current(['id' => null]));
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

        //In case there is no controller, throw an exception
        $this->removeMockedAction();

        $this->setExpectedException('yii\base\InvalidParamException');
        Url::to(['site/view']);
    }

    public function testBase()
    {
        $this->mockAction('page', 'view', null, ['id' => 10]);
        $this->assertEquals('/base', Url::base());
        $this->assertEquals('http://example.com/base', Url::base(true));
        $this->assertEquals('https://example.com/base', Url::base('https'));
    }

    public function testHome()
    {
        $this->assertEquals('/base/index.php', Url::home());
        $this->assertEquals('http://example.com/base/index.php', Url::home(true));
        $this->assertEquals('https://example.com/base/index.php', Url::home('https'));
    }

    public function testCanonical()
    {
        $this->mockAction('page', 'view', null, ['id' => 10]);
        $this->assertEquals('http://example.com/base/index.php?r=page%2Fview&id=10', Url::canonical());
        $this->removeMockedAction();
    }
}
