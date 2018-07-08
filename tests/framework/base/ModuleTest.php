<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use Yii;
use yii\base\BaseObject;
use yii\base\Controller;
use yii\base\Module;
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
        $this->assertEquals(__DIR__ . DIRECTORY_SEPARATOR . 'controllers', str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $module->controllerPath));
    }

    public function testSetupVersion()
    {
        $module = new TestModule('test');

        $version = '1.0.1';
        $module->setVersion($version);
        $this->assertEquals($version, $module->getVersion());

        $module->setVersion(function ($module) {
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


    public function testServiceLocatorTraversal()
    {
        $parent = new Module('parent');
        $child = new Module('child', $parent);
        $grandchild = new Module('grandchild', $child);

        $parentObject = new BaseObject();
        $childObject = new BaseObject();

        $parent->set('test', $parentObject);
        $this->assertTrue($grandchild->has('test'));
        $this->assertTrue($child->has('test'));
        $this->assertTrue($parent->has('test'));
        $this->assertSame($parentObject, $grandchild->get('test'));
        $this->assertSame($parentObject, $child->get('test'));
        $this->assertSame($parentObject, $parent->get('test'));

        $child->set('test', $childObject);
        $this->assertSame($childObject, $grandchild->get('test'));
        $this->assertSame($childObject, $child->get('test'));
        $this->assertSame($parentObject, $parent->get('test'));
        $this->assertTrue($grandchild->has('test'));
        $this->assertTrue($child->has('test'));
        $this->assertTrue($parent->has('test'));

        $parent->clear('test');
        $this->assertSame($childObject, $grandchild->get('test'));
        $this->assertSame($childObject, $child->get('test'));
        $this->assertTrue($grandchild->has('test'));
        $this->assertTrue($child->has('test'));
        $this->assertFalse($parent->has('test'));
    }

    public function testCreateControllerByID()
    {
        $module = new TestModule('test');
        $module->controllerNamespace = 'yiiunit\framework\base';

        $route = 'module-test';
        $this->assertInstanceOf(ModuleTestController::class, $module->createControllerByID($route));

        $route = 'module-test-';
        $this->assertNotInstanceOf(ModuleTestController::class, $module->createControllerByID($route));

        $route = '-module-test';
        $this->assertNotInstanceOf(ModuleTestController::class, $module->createControllerByID($route));

        $route = 'very-complex-name-test';
        $this->assertInstanceOf(VeryComplexNameTestController::class, $module->createControllerByID($route));

        $route = 'very-complex-name-test--';
        $this->assertNotInstanceOf(VeryComplexNameTestController::class, $module->createControllerByID($route));

        $route = '--very-complex-name-test';
        $this->assertNotInstanceOf(VeryComplexNameTestController::class, $module->createControllerByID($route));

        $route = 'very---complex---name---test';
        $this->assertNotInstanceOf(VeryComplexNameTestController::class, $module->createControllerByID($route));
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

class VeryComplexNameTestController extends Controller
{
    public function actionIndex()
    {
        ModuleTest::$actionRuns[] = $this->action->uniqueId;
    }
}
