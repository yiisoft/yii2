<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use Yii;
use yii\base\Controller;
use yii\base\Module;
use yii\base\Object;
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
        $this->assertSame('yiiunit\framework\base\controllers', $module->controllerNamespace);
        $this->assertSame(__DIR__ . DIRECTORY_SEPARATOR . 'controllers', str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $module->controllerPath));
    }

    public function testSetupVersion()
    {
        $module = new TestModule('test');

        $version = '1.0.1';
        $module->setVersion($version);
        $this->assertSame($version, $module->getVersion());

        $module->setVersion(function ($module) {
            /* @var $module TestModule */
            return 'version.' . $module->getUniqueId();
        });
        $this->assertSame('version.test', $module->getVersion());
    }

    /**
     * @depends testSetupVersion
     */
    public function testDefaultVersion()
    {
        $module = new TestModule('test');

        $version = $module->getVersion();
        $this->assertSame('1.0', $version);
    }

    public static $actionRuns = [];

    public function testRunControllerAction()
    {
        $module = new TestModule('test');
        $this->assertNull(Yii::$app->controller);
        static::$actionRuns = [];

        $module->runAction('test-controller1/test1');
        $this->assertSame([
            'test/test-controller1/test1',
        ], static::$actionRuns);
        $this->assertNotNull(Yii::$app->controller);
        $this->assertSame('test-controller1', Yii::$app->controller->id);
        $this->assertSame('test/test-controller1', Yii::$app->controller->uniqueId);
        $this->assertNotNull(Yii::$app->controller->action);
        $this->assertSame('test/test-controller1/test1', Yii::$app->controller->action->uniqueId);

        $module->runAction('test-controller2/test2');
        $this->assertSame([
            'test/test-controller1/test1',
            'test/test-controller2/test2',
        ], static::$actionRuns);
        $this->assertNotNull(Yii::$app->controller);
        $this->assertSame('test-controller1', Yii::$app->controller->id);
        $this->assertSame('test/test-controller1', Yii::$app->controller->uniqueId);
        $this->assertNotNull(Yii::$app->controller->action);
        $this->assertSame('test/test-controller1/test1', Yii::$app->controller->action->uniqueId);
    }


    public function testServiceLocatorTraversal()
    {
        $parent = new Module('parent');
        $child = new Module('child', $parent);
        $grandchild = new Module('grandchild', $child);

        $parent->set('test', new Object());
        $this->assertInstanceOf(Object::className(), $grandchild->get('test'));
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
