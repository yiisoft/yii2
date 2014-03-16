<?php
namespace yiiunit\framework\helpers;

use yii\base\Action;
use yii\base\Module;
use yii\helpers\Url;
use yii\web\Controller;
use yiiunit\TestCase;

/**
 * UrlTest
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
                    'url' => '/base/index.php&r=site/current&id=42'
                ],
            ],
        ], '\yii\web\Application');
    }

    /**
     * Mocks controller action with parameters
     *
     * @param string $controllerId
     * @param string $actionId
     * @param string $moduleID
     * @param array  $params
     */
    protected function mockAction($controllerId, $actionId, $moduleID = null, $params = [])
    {
        \Yii::$app->controller = $controller = new Controller($controllerId, \Yii::$app);
        $controller->actionParams = $params;
        $controller->action = new Action($actionId, $controller);

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
        $this->assertEquals('/base/index.php?r=page/view', Url::toRoute(''));
        $this->assertEquals('http://example.com/base/index.php?r=page/view', Url::toRoute('', true));
        $this->assertEquals('https://example.com/base/index.php?r=page/view', Url::toRoute('', 'https'));

        // If the route contains no slashes at all, it is considered to be an action ID of the current controller and
        // will be prepended with uniqueId;
        $this->assertEquals('/base/index.php?r=page/edit', Url::toRoute('edit'));
        $this->assertEquals('/base/index.php?r=page/edit&id=20', Url::toRoute(['edit', 'id' => 20]));
        $this->assertEquals('http://example.com/base/index.php?r=page/edit&id=20', Url::toRoute(['edit', 'id' => 20], true));
        $this->assertEquals('https://example.com/base/index.php?r=page/edit&id=20', Url::toRoute(['edit', 'id' => 20], 'https'));

        // If the route has no leading slash, it is considered to be a route relative
        // to the current module and will be prepended with the module's uniqueId.
        $this->mockAction('default', 'index', 'stats');
        $this->assertEquals('/base/index.php?r=stats/user/view', Url::toRoute('user/view'));
        $this->assertEquals('/base/index.php?r=stats/user/view&id=42', Url::toRoute(['user/view', 'id' => 42]));
        $this->assertEquals('http://example.com/base/index.php?r=stats/user/view&id=42', Url::toRoute(['user/view', 'id' => 42], true));
        $this->assertEquals('https://example.com/base/index.php?r=stats/user/view&id=42', Url::toRoute(['user/view', 'id' => 42], 'https'));

        // In case there is no controller, [[\yii\web\UrlManager::createUrl()]] will be used.
        $this->removeMockedAction();

        $this->assertEquals('/base/index.php?r=site/view', Url::toRoute('site/view'));
        $this->assertEquals('http://example.com/base/index.php?r=site/view', Url::toRoute('site/view', true));
        $this->assertEquals('https://example.com/base/index.php?r=site/view', Url::toRoute('site/view', 'https'));
        $this->assertEquals('/base/index.php?r=site/view&id=37', Url::toRoute(['site/view', 'id' => 37]));
    }

    public function testTo()
    {
        // is an array: the first array element is considered a route, while the rest of the name-value
        // pairs are treated as the parameters to be used for URL creation using Url::toRoute.
        $this->mockAction('page', 'view', null, ['id' => 10]);
        $this->assertEquals('/base/index.php?r=page/edit&id=20', Url::to(['edit', 'id' => 20]));
        $this->assertEquals('/base/index.php?r=page/edit', Url::to(['edit']));
        $this->assertEquals('/base/index.php?r=page/view', Url::to(['']));

        $this->assertEquals('http://example.com/base/index.php?r=page/edit&id=20', Url::to(['edit', 'id' => 20], true));
        $this->assertEquals('http://example.com/base/index.php?r=page/edit', Url::to(['edit'], true));
        $this->assertEquals('http://example.com/base/index.php?r=page/view', Url::to([''], true));

        $this->assertEquals('https://example.com/base/index.php?r=page/edit&id=20', Url::to(['edit', 'id' => 20], 'https'));
        $this->assertEquals('https://example.com/base/index.php?r=page/edit', Url::to(['edit'], 'https'));
        $this->assertEquals('https://example.com/base/index.php?r=page/view', Url::to([''], 'https'));

        //In case there is no controller, [[\yii\web\UrlManager::createUrl()]] will be used.
        $this->removeMockedAction();

        $this->assertEquals('/base/index.php?r=edit&id=20', Url::to(['edit', 'id' => 20]));
        $this->assertEquals('/base/index.php?r=edit', Url::to(['edit']));
        $this->assertEquals('/base/index.php?r=', Url::to(['']));

        $this->assertEquals('http://example.com/base/index.php?r=edit&id=20', Url::to(['edit', 'id' => 20], true));
        $this->assertEquals('http://example.com/base/index.php?r=edit', Url::to(['edit'], true));
        $this->assertEquals('http://example.com/base/index.php?r=', Url::to([''], true));

        $this->assertEquals('https://example.com/base/index.php?r=edit&id=20', Url::to(['edit', 'id' => 20], 'https'));
        $this->assertEquals('https://example.com/base/index.php?r=edit', Url::to(['edit'], 'https'));
        $this->assertEquals('https://example.com/base/index.php?r=', Url::to([''], 'https'));

        // is an empty string: the currently requested URL will be returned;
        $this->mockAction('page', 'view', null, ['id' => 10]);
        $this->assertEquals('/base/index.php&r=site/current&id=42', Url::to(''));
        $this->assertEquals('http://example.com/base/index.php&r=site/current&id=42', Url::to('', true));
        $this->assertEquals('https://example.com/base/index.php&r=site/current&id=42', Url::to('', 'https'));
        $this->removeMockedAction();

        // is a non-empty string: it will first be processed by [[Yii::getAlias()]]. If the result
        // is an absolute URL, it will be returned either without any change or, if schema was specified, with schema
        // replaced; Otherwise, the result will be prefixed with [[\yii\web\Request::baseUrl]] and returned.
        \Yii::setAlias('@web1', 'http://test.example.com/test/me1');
        \Yii::setAlias('@web2', 'test/me2');
        \Yii::setAlias('@web3', '');
        \Yii::setAlias('@web4', '/test');
        \Yii::setAlias('@web5', '#test');

        $this->assertEquals('http://test.example.com/test/me1', Url::to('@web1'));
        $this->assertEquals('http://test.example.com/test/me1', Url::to('@web1', true));
        $this->assertEquals('https://test.example.com/test/me1', Url::to('@web1', 'https'));

        $this->assertEquals('/base/test/me2', Url::to('@web2'));
        $this->assertEquals('http://example.com/base/test/me2', Url::to('@web2', true));
        $this->assertEquals('https://example.com/base/test/me2', Url::to('@web2', 'https'));

        $this->assertEquals('/base/', Url::to('@web3'));
        $this->assertEquals('http://example.com/base/', Url::to('@web3', true));
        $this->assertEquals('https://example.com/base/', Url::to('@web3', 'https'));

        $this->assertEquals('/test', Url::to('@web4'));
        $this->assertEquals('http://example.com/test', Url::to('@web4', true));
        $this->assertEquals('https://example.com/test', Url::to('@web4', 'https'));

        $this->assertEquals('#test', Url::to('@web5'));
        $this->assertEquals('http://example.com#test', Url::to('@web5', true));
        $this->assertEquals('https://example.com#test', Url::to('@web5', 'https'));
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
        $this->assertEquals('http://example.com/base/index.php?r=page/view&id=10', Url::canonical());
        $this->removeMockedAction();
    }
}
