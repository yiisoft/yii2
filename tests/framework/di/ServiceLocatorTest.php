<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di;

use yii\base\Object;
use yii\di\ServiceLocator;
use yiiunit\TestCase;

class Creator
{
    public static function create()
    {
        return new TestClass;
    }
}

class TestClass extends Object
{
    public $prop1 = 1;
    public $prop2;
}

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ServiceLocatorTest extends TestCase
{
    public function testCallable()
    {
        // anonymous function
        $container = new ServiceLocator;
        $className = TestClass::className();
        $container->set($className, function () {
            return new TestClass([
                'prop1' => 100,
                'prop2' => 200,
            ]);
        });
        $object = $container->get($className);
        $this->assertTrue($object instanceof $className);
        $this->assertEquals(100, $object->prop1);
        $this->assertEquals(200, $object->prop2);

        // static method
        $container = new ServiceLocator;
        $className = TestClass::className();
        $container->set($className, [__NAMESPACE__ . "\\Creator", 'create']);
        $object = $container->get($className);
        $this->assertTrue($object instanceof $className);
        $this->assertEquals(1, $object->prop1);
        $this->assertNull($object->prop2);
    }

    public function testObject()
    {
        $object = new TestClass;
        $className = TestClass::className();
        $container = new ServiceLocator;
        $container->set($className, $object);
        $this->assertTrue($container->get($className) === $object);
    }

    public function testShared()
    {
        // with configuration: shared
        $container = new ServiceLocator;
        $className = TestClass::className();
        $container->set($className, [
            'class' => $className,
            'prop1' => 10,
            'prop2' => 20,
        ]);
        $object = $container->get($className);
        $this->assertEquals(10, $object->prop1);
        $this->assertEquals(20, $object->prop2);
        $this->assertTrue($object instanceof $className);
        // check shared
        $object2 = $container->get($className);
        $this->assertTrue($object2 instanceof $className);
        $this->assertTrue($object === $object2);
    }
}
