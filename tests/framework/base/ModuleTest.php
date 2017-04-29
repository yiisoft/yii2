<?php

namespace yiiunit\framework\base;

use Yii;
use yii\base\Controller;
use yiiunit\TestCase;

/**
 * @group base
 */
class ModuleTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testControllerPath()
    {
        $module = new TestModule('test');
        $this->assertEquals('yiiunit\framework\base\controllers', $module->controllerNamespace);
        $this->assertEquals(__DIR__ . DIRECTORY_SEPARATOR . 'controllers', str_replace(['/','\\'], DIRECTORY_SEPARATOR , $module->controllerPath));
    }

    public function testSetupVersion()
    {
        $module = new TestModule('test');

        $version = '1.0.1';
        $module->setVersion($version);
        $this->assertEquals($version, $module->getVersion());

        $module->setVersion(function($module) {
            /* @var $module TestModule */
            return 'version.' . $module->getUniqueId();
        });
        $this->assertEquals('version.test', $module->getVersion());
    }

    /**
     * @depends testSetupVersion
     */
    public function testDefaultVersion()
    {
        $module = new TestModule('test');

        $version = $module->getVersion();
        $this->assertEquals('1.0', $version);
    }

    public static $actionRuns = [];

    public function testRunControllerAction()
    {
        $module = new TestModule('test');
        $this->assertNull(Yii::$app->controller);
        static::$actionRuns = [];

        $module->runAction('test-controller1/test1');
        $this->assertEquals([
            'test/test-controller1/test1',
        ], static::$actionRuns);
        $this->assertNotNull(Yii::$app->controller);
        $this->assertEquals('test-controller1', Yii::$app->controller->id);
        $this->assertEquals('test/test-controller1', Yii::$app->controller->uniqueId);
        $this->assertNotNull(Yii::$app->controller->action);
        $this->assertEquals('test/test-controller1/test1', Yii::$app->controller->action->uniqueId);

        $module->runAction('test-controller2/test2');
        $this->assertEquals([
            'test/test-controller1/test1',
            'test/test-controller2/test2',
        ], static::$actionRuns);
        $this->assertNotNull(Yii::$app->controller);
        $this->assertEquals('test-controller1', Yii::$app->controller->id);
        $this->assertEquals('test/test-controller1', Yii::$app->controller->uniqueId);
        $this->assertNotNull(Yii::$app->controller->action);
        $this->assertEquals('test/test-controller1/test1', Yii::$app->controller->action->uniqueId);
    }

    public function testFindLayoutFileFilledLayoutParamString()
    {        
        $module = $this->makeTestModule();
        $expected = $module->layoutPath . DIRECTORY_SEPARATOR . 'baseLayout.php';

        $actual = $module->findLayoutFile('baseLayout', 'php');

        $this->assertEquals($expected, $actual);
    }

    public function testFindLayoutFileFilledLayoutParamFalse()
    {
        $module = $this->makeTestModule();

        $actual = $module->findLayoutFile(false, 'php');

        $this->assertFalse($actual);
    }

    public function testFindLayoutFileInModule()
    {
        $parentModule = $this->makeTestModule('parent', null, ['layoutPath' => '@app/framework/layouts']);
        $childModule  = $this->makeTestModule('child', $parentModule);
        $expected = $childModule->layoutPath . DIRECTORY_SEPARATOR . 'main.php';

        $actual = $childModule->findLayoutFile(null, 'php');

        $this->assertEquals($expected, $actual);
    }

    public function testFindLayoutFileInParentModule()
    {
        $parentModule = $this->makeTestModule('parent', null, ['layoutPath' => '@app/framework/layouts']);
        $childModule  = $this->makeTestModule('child' , $parentModule, ['layout' => null]);
        $expected = $parentModule->layoutPath . DIRECTORY_SEPARATOR . 'main.php';

        $actual = $childModule->findLayoutFile(null, 'php');

        $this->assertEquals($expected, $actual);
    }

    public function testFindLayoutFileAliasPathLayout()
    {
        $module = $this->makeTestModule();
        $expected = $module->layoutPath . DIRECTORY_SEPARATOR . 'main.php';

        $actual = $module->findLayoutFile('@app/framework/base/fixtures/main', 'php');
        
        $this->assertEquals($expected, $actual);
    }

    public function testFindLayoutFileAbsolutePathLayout()
    {
        $module = $this->makeTestModule();
        $expected = $module->layoutPath . DIRECTORY_SEPARATOR . 'main.php';

        $actual = $module->findLayoutFile('/main', 'php');

        $this->assertEquals($expected, $actual);
    }

    public function testFindLayoutFileExtensionTpl()
    {
        $module = $this->makeTestModule();
        $expected = $module->layoutPath . DIRECTORY_SEPARATOR . 'main.tpl';

        $actual = $module->findLayoutFile('main', 'tpl');

        $this->assertEquals($expected, $actual);
    }

    public function testFindLayoutViewExtensionPHP5AndFileNotExists()
    {
        $module = $this->makeTestModule();
        $expected = $module->layoutPath . DIRECTORY_SEPARATOR . 'main.php';

        $actual = $module->findLayoutFile(null, 'php5');

        $this->assertEquals($expected, $actual);
    }

    private function makeTestModule($name = 'test', \yii\base\Module $parent = null, array $config = [])
    {
        $config = array_merge(
            [
                'layout'     => 'main',
                'layoutPath' => '@app/framework/base/fixtures',
                'viewPath'   => '@app/framework/base/fixtures',
            ],
            $config
        );

        return new TestModule($name, $parent, $config);
    }
}

class TestModule extends \yii\base\Module
{
    public $controllerMap = [
        'test-controller1' => 'yiiunit\framework\base\ModuleTestController',
        'test-controller2' => 'yiiunit\framework\base\ModuleTestController',
    ];
}

class ModuleTestController extends Controller
{
    public function actionTest1()
    {
        ModuleTest::$actionRuns[] = $this->action->uniqueId;
    }
    public function actionTest2()
    {
        ModuleTest::$actionRuns[] = $this->action->uniqueId;
    }
}