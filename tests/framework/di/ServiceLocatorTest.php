<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di;

use yii\base\BaseObject;
use yii\di\ServiceLocator;
use yii\widgets\InputWidget;
use yiiunit\TestCase;

class Creator
{
    public static function create()
    {
        return new TestClass;
    }
}

class TestClass extends BaseObject
{
    public $prop1 = 1;
    public $prop2;
}

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 * @group di
 */
class ServiceLocatorTest extends TestCase
{
    public function testCallable()
    {
        // anonymous function
        $container = new ServiceLocator();
        $className = TestClass::class;
        $container->set($className, function () {
            return new TestClass([
                'prop1' => 100,
                'prop2' => 200,
            ]);
        });
        $object = $container->get($className);
        $this->assertInstanceOf($className, $object);
        $this->assertEquals(100, $object->prop1);
        $this->assertEquals(200, $object->prop2);

        // static method
        $container = new ServiceLocator();
        $className = TestClass::class;
        $container->set($className, [__NAMESPACE__ . "\\Creator", 'create']);
        $object = $container->get($className);
        $this->assertInstanceOf($className, $object);
        $this->assertEquals(1, $object->prop1);
        $this->assertNull($object->prop2);
    }

    public function testObject()
    {
        $object = new TestClass();
        $className = TestClass::class;
        $container = new ServiceLocator;
        $container->set($className, $object);
        $this->assertSame($container->get($className), $object);
    }

    public function testShared()
    {
        // with configuration: shared
        $container = new ServiceLocator();
        $className = TestClass::class;
        $container->set($className, [
            '__class' => $className,
            'prop1' => 10,
            'prop2' => 20,
        ]);
        $object = $container->get($className);
        $this->assertEquals(10, $object->prop1);
        $this->assertEquals(20, $object->prop2);
        $this->assertInstanceOf($className, $object);
        // check shared
        $object2 = $container->get($className);
        $this->assertInstanceOf($className, $object2);
        $this->assertSame($object, $object2);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/11771
     */
    public function testModulePropertyIsset()
    {
        $config = [
            'components' => [
                'captcha' => [
                    'name' => 'foo bar',
                    '__class' => InputWidget::class,
                ],
            ],
        ];

        $app = new ServiceLocator($config);

        $this->assertTrue(isset($app->captcha->name));
        $this->assertNotEmpty($app->captcha->name);

        $this->assertEquals('foo bar', $app->captcha->name);

        $this->assertTrue(isset($app->captcha->name));
        $this->assertNotEmpty($app->captcha->name);
    }
}
