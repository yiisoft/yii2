<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di;

use yii\base\Object;
use yii\di\Container;
use yiiunit\TestCase;

class Creator
{
    public static function create($type, $container)
    {
        return new $type;
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
class ContainerTest extends TestCase
{
    public function testDefault()
    {
        // without configuring anything
        $container = new Container;
        $className = TestClass::className();
        $object = $container->get($className);
        $this->assertEquals(1, $object->prop1);
        $this->assertTrue($object instanceof $className);
        // check non-shared
        $object2 = $container->get($className);
        $this->assertTrue($object2 instanceof $className);
        $this->assertTrue($object !== $object2);
    }

    public function testCallable()
    {
        // anonymous function
        $container = new Container;
        $className = TestClass::className();
        $container->set($className, function ($type) {
            return new $type([
                'prop1' => 100,
                'prop2' => 200,
            ]);
        });
        $object = $container->get($className);
        $this->assertTrue($object instanceof $className);
        $this->assertEquals(100, $object->prop1);
        $this->assertEquals(200, $object->prop2);

        // static method
        $container = new Container;
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
        $container = new Container;
        $container->set($className, $object);
        $this->assertTrue($container->get($className) === $object);
    }

    public function testString()
    {
        $object = new TestClass;
        $className = TestClass::className();
        $container = new Container;
        $container->set('test', $object);
        $container->set($className, 'test');
        $this->assertTrue($container->get($className) === $object);
    }

    public function testShared()
    {
        // with configuration: shared
        $container = new Container;
        $className = TestClass::className();
        $container->set($className, [
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

    public function testNonShared()
    {
        // with configuration: non-shared
        $container = new Container;
        $className = TestClass::className();
        $container->set('*' . $className, [
            'prop1' => 10,
            'prop2' => 20,
        ]);
        $object = $container->get($className);
        $this->assertEquals(10, $object->prop1);
        $this->assertEquals(20, $object->prop2);
        $this->assertTrue($object instanceof $className);
        // check non-shared
        $object2 = $container->get($className);
        $this->assertTrue($object2 instanceof $className);
        $this->assertTrue($object !== $object2);

        // shared as non-shared
        $object = new TestClass;
        $className = TestClass::className();
        $container = new Container;
        $container->set('*' . $className, $object);
        $this->assertTrue($container->get($className) === $object);
    }

    public function testRegisterByID()
    {
        $className = TestClass::className();
        $container = new Container;
        $container->set('test', [
            'class' => $className,
            'prop1' => 100,
        ]);
        $object = $container->get('test');
        $this->assertTrue($object instanceof TestClass);
        $this->assertEquals(100, $object->prop1);
    }
}
